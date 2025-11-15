<?php

/*
 * MIT License
 * Copyright (c) 2025 ObviousAIChicken
 * github.com/obviousaichicken/zabbix_widgets
 */

namespace Modules\HostOverview\Actions;

use API;
use CControllerDashboardWidgetView;
use CControllerResponseData;
use Modules\HostOverview\Includes\WidgetForm;

class WidgetView extends CControllerDashboardWidgetView
{
    protected function doAction(): void
    {
        // Collect metrics
        $metrics = $this->collectMetrics();

        if (! empty($metrics)) {
            // Process metrics
            $cpu          = $this->computeCpu($metrics);
            $ram          = $this->computeRam($metrics);
            $load         = $this->computeLoad($metrics);
            $load_percent = $this->computeLoadPercent($load);
            $disks        = $this->buildDisks($metrics);
            $interfaces   = $this->buildInterfaces($metrics);
            $partitions   = $this->buildPartitions($metrics);

            // Format response
            $this->setResponse(new CControllerResponseData([
                'name'         => $this->getInput('name', $this->widget->getName()),
                'cpu'          => $cpu,
                'ram'          => $ram,
                'load_percent' => $load_percent,
                'load'         => $load,
                'interfaces'   => $interfaces,
                'disks'        => $disks,
                'partitions'   => $partitions,
                'config'       => $this->fields_values,
            ]));
        }
    }

    ###############################################
    ### FUNCTIONS
    ###############################################

    // Collect last values for relevant items on the selected host
    private function collectMetrics(): array
    {
        // Name filters; API will match any of them
        $name_filters = [
            'Load average (5m avg)',
            'Memory utilization',
            'CPU utilization',
            'Disk utilization',
            'Used, in %',
            'Space: Used',
            'Bits',
        ];

        // Retrieve from API
        $items = API::Item()->get([
            'output'      => ['itemid', 'name', 'lastvalue'],
            'hostids'     => $this->fields_values['hostid'],
            'search'      => ['name' => $name_filters],
            'searchByAny' => true,
        ]);

        // Build metrics so downstream builders can filter by key
        $metrics = [];
        foreach ($items as $item) {
            $name           = $item['name'];
            $val            = $item['lastvalue'] ?? null;
            $num            = is_numeric($val) ? (float) $val : null;
            $metrics[$name] = ['value' => $num];
        }

        return $metrics;
    }

    // Compute CPU utilization
    private function computeCpu(array $metrics): int
    {
        $value = $metrics['CPU utilization']['value'] ?? 0;

        return $this->clampPercent($value);
    }

    // Compute RAM utilization
    private function computeRam(array $metrics): int
    {
        $value = $metrics['Memory utilization']['value'] ?? 0;

        return $this->clampPercent($value);
    }

    // Compute load (5m average)
    private function computeLoad(array $metrics): float
    {
        $value = $metrics['Load average (5m avg)']['value'] ?? 0;

        return (float) $value;
    }

    // Compute load percent (relative to configured high)
    private function computeLoadPercent(float $load): int
    {
        $load_high = (int) ($this->fields_values['load_high'] ?? 0);

        if ($load_high <= 0) {
            return 0;
        }

        $percent = ($load / $load_high) * 100;

        return $this->clampPercent($percent);
    }

    // Build disk utilization rows
    private function buildDisks(array $metrics): array
    {
        $rows  = [];
        $index = [];

        foreach ($metrics as $key => $details) {
            // Filter for disk utilization metrics
            if (! str_contains($key, 'Disk utilization')) {
                continue;
            }

            // Extract disk name before the first colon and strip non-letters
            // "sda: Disk utilization" -> "sda"
            $name_part = explode(':', $key, 2)[0];
            $disk_name = preg_replace('/[^a-zA-Z]/', '', $name_part);

            // Fallback to questionmark if name is empty
            if (empty($disk_name)) {
                $disk_name = '?';
            }

            $percent = $this->clampPercent($details['value'] ?? 0);

            if (array_key_exists($disk_name, $index)) {
                $row_index                   = $index[$disk_name];
                $rows[$row_index]['percent'] = $percent;
            } else {
                $index[$disk_name] = count($rows);
                $rows[]            = [
                    'name'    => $disk_name,
                    'percent' => $percent,
                ];
            }
        }

        return $rows;
    }

    // Build interface bitrate rows
    private function buildInterfaces(array $metrics): array
    {
        $rows              = [];
        $alias_counter     = 1;
        $interface_aliases = [];

        $interfaces_high = (int) ($this->fields_values['interfaces_high'] ?? 0);
        $interfaces_unit = (int) ($this->fields_values['interfaces_unit'] ?? WidgetForm::INTERFACES_UNIT_KBPS);

        $factor = match ($interfaces_unit) {
            WidgetForm::INTERFACES_UNIT_GBPS => 1_000_000_000,
            WidgetForm::INTERFACES_UNIT_MBPS => 1_000_000,
            default                          => 1_000,
        };

        // Compute capacity in bps based on configured high value and unit
        $capacity = $interfaces_high > 0 ? $interfaces_high * $factor : 0;

        foreach ($metrics as $key => $details) {
            // Filter for interface bitrate metics
            if (! str_contains($key, 'Bits')) {
                continue;
            }

            // Extract interface name:
            // "Interface eth0: Bits ..." -> "eth0"
            $interface_name = preg_match('/^Interface (.+?): Bits/', $key, $match) ? $match[1] : null;

            // Fallback to questionmark if name is empty
            if (empty($interface_name)) {
                $interface_name = '?';
            }

            // Check if bitrate is received or sent
            if (str_ends_with($key, 'received')) {
                $direction = 'received';
            } elseif (str_ends_with($key, 'sent')) {
                $direction = 'sent';
            } else {
                continue;
            }

            // Apply short alias for long interface names
            if (strlen($interface_name) > 4) {
                $label = $interface_aliases[$interface_name] ??= 'IF' . $alias_counter++;
            } else {
                $label = $interface_name;
            }

            $bps     = $details['value'] ?? 0;
            $percent = 0;

            if ($capacity > 0 && is_numeric($bps)) {
                $percent = $this->clampPercent(($bps / $capacity) * 100);
            }

            // Build the final display name
            $suffix       = $direction === 'sent' ? 'TX' : 'RX';
            $display_name = strtoupper($label . ' ' . $suffix);

            $rows[$display_name] = [
                'bps'     => $bps,
                'percent' => $percent,
            ];
        }

        return $rows;
    }

    // Build partition usage rows
    private function buildPartitions(array $metrics): array
    {
        $rows  = [];
        $index = [];

        foreach ($metrics as $key => $details) {
            // Filter for partitions
            if (! str_contains($key, 'Used, in %')) {
                continue;
            }

            // Extract partition name from square brackets
            // "[/var]" → "/var"
            $partition_name = preg_match('/\[(.*?)\]/', $key, $matches) ? $matches[1] : null;

            // Skip if the partition has no name
            if (empty($partition_name)) {
                $partition_name = '?';
            }

            $percent = $this->clampPercent($details['value'] ?? 0);

            if (array_key_exists($partition_name, $index)) {
                $row_index                   = $index[$partition_name];
                $rows[$row_index]['percent'] = $percent;
            } else {
                $index[$partition_name] = count($rows);
                $rows[]                 = [
                    'name'    => $partition_name,
                    'percent' => $percent,
                ];
            }
        }

        return $rows;
    }

    ###############################################
    ### HELPERS
    ###############################################

    private function clampPercent(float | int $value): int
    {
        return max(0, min(100, (int) round($value)));
    }

}
