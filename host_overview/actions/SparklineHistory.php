<?php

/*
 * MIT License
 * Copyright (c) 2026 ObviousAIChicken
 * github.com/obviousaichicken/zabbix_widgets
 */

namespace Modules\HostOverview\Actions;

use API;
use CController;
use CControllerResponseData;
use Modules\HostOverview\Includes\MetricMatcher;
use RuntimeException;
use Throwable;

class SparklineHistory extends CController
{
    public const PERIODS = [
        '1h' => 3600,
        '3h' => 10800,
        '12h' => 43200,
        '1d' => 86400,
        '3d' => 259200,
        '1w' => 604800,
        '30d' => 2592000,
    ];

    private const MAX_POINTS = 200;
    private const TREND_BLEND_SECONDS = 43200;
    private const RECENT_HISTORY_SECONDS = 7200;
    private const HISTORY_GAP_FLOOR = 300;
    private const TREND_GAP_FLOOR = 3600;
    private const VALUE_TYPE_FLOAT = 0;
    private const VALUE_TYPE_UINT64 = 3;

    protected function init(): void
    {
        $this->setPostContentType(self::POST_CONTENT_TYPE_JSON);
        $this->disableCsrfValidation();
    }

    protected function checkPermissions(): bool
    {
        return $this->getUserType() >= USER_TYPE_ZABBIX_USER;
    }

    protected function checkInput(): bool
    {
        $fields = [
            'hostid' => 'required|db hosts.hostid',
            'period' => 'required|in ' . implode(',', array_keys(self::PERIODS)),
            'itemid' => 'string',
            'item_name' => 'string',
            'value_type' => 'string',
            'display_kind' => 'required|in percent,load,interface',
            'axis_min' => 'string',
            'axis_max' => 'string',
            'invert_percent' => 'in 0,1',
        ];

        $ret = $this->validateInput($fields);

        if (!$ret) {
            $this->setErrorResponse(array_column(get_and_clear_messages(), 'message'));
        }

        return $ret;
    }

    protected function doAction(): void
    {
        try {
            $this->setJsonResponse($this->build([
                'hostid' => $this->getInput('hostid'),
                'period' => $this->getInput('period'),
                'itemid' => $this->getInput('itemid', ''),
                'item_name' => $this->getInput('item_name', ''),
                'value_type' => $this->getInput('value_type', ''),
                'display_kind' => $this->getInput('display_kind', 'percent'),
                'axis_min' => $this->getInput('axis_min', ''),
                'axis_max' => $this->getInput('axis_max', ''),
                'invert_percent' => $this->getInput('invert_percent', '0'),
            ]));
        }
        catch (Throwable $exception) {
            $message = trim($exception->getMessage()) !== ''
                ? $exception->getMessage()
                : 'Could not load sparkline data.';

            $this->setErrorResponse([$message]);
        }
    }

    private function setJsonResponse(array $payload): void
    {
        $this->setResponse(
            (new CControllerResponseData([
                'main_block' => json_encode($payload, JSON_THROW_ON_ERROR),
            ]))->disableView()
        );
    }

    private function setErrorResponse(array $messages): void
    {
        $this->setJsonResponse([
            'error' => [
                'messages' => array_values(array_filter($messages, static fn($message): bool => $message !== '')),
            ],
        ]);
    }

    private function build(array $options): array
    {
        $request = $this->normalizeRequest($options);
        $item = $this->resolveItem($request);

        if ($item === null) {
            throw new RuntimeException('Item not found');
        }

        $seconds = self::PERIODS[$request['period']];
        $time_till = time();
        $time_from = $time_till - $seconds;
        $series = $this->loadSeries(
            (string) $item['itemid'],
            (int) $item['value_type'],
            $time_from,
            $time_till,
            $seconds
        );

        $points = $this->applyMetricTransforms($series['points'], $request);
        $points = $this->downsamplePoints($points);
        [$min, $max] = $this->calculateBounds($points, $request);

        return [
            'item_ref' => $this->toItemRef($item),
            'points' => $points,
            'min' => $min,
            'max' => $max,
            'timeFrom' => $time_from,
            'timeTill' => $time_till,
            'gapThresholdFloor' => $series['gapThresholdFloor'],
        ];
    }

    private function normalizeRequest(array $options): array
    {
        $period = trim((string) ($options['period'] ?? ''));

        if (!array_key_exists($period, self::PERIODS)) {
            throw new RuntimeException('Unsupported period');
        }

        return [
            'hostid' => trim((string) ($options['hostid'] ?? '')),
            'period' => $period,
            'itemid' => $this->normalizeOptionalString($options['itemid'] ?? null),
            'item_name' => $this->normalizeOptionalString($options['item_name'] ?? null),
            'value_type' => $this->normalizeOptionalInt($options['value_type'] ?? null),
            'display_kind' => trim((string) ($options['display_kind'] ?? 'percent')),
            'axis_min' => $this->normalizeOptionalFloat($options['axis_min'] ?? null),
            'axis_max' => $this->normalizeOptionalFloat($options['axis_max'] ?? null),
            'invert_percent' => $this->normalizeBoolFlag($options['invert_percent'] ?? null),
        ];
    }

    private function resolveItem(array $request): ?array
    {
        if ($request['hostid'] === '') {
            throw new RuntimeException('No host');
        }

        if ($request['itemid'] !== null) {
            $items = API::Item()->get([
                'output' => ['itemid', 'name', 'value_type'],
                'hostids' => [$request['hostid']],
                'itemids' => [$request['itemid']],
                'limit' => 1,
            ]);

            if ($items !== []) {
                return [
                    'itemid' => (string) $items[0]['itemid'],
                    'name' => (string) ($items[0]['name'] ?? ''),
                    'value_type' => (int) ($items[0]['value_type'] ?? 0),
                ];
            }
        }

        if ($request['item_name'] === null) {
            return null;
        }

        $matcher = new MetricMatcher();
        $collection = $matcher->collect([$request['hostid']], [$request['item_name']]);

        return $matcher->resolve($collection['metrics'], $request['item_name']);
    }

    private function loadSeries(
        string $itemid,
        int $value_type,
        int $time_from,
        int $time_till,
        int $seconds
    ): array {
        $supports_trends = in_array($value_type, [self::VALUE_TYPE_FLOAT, self::VALUE_TYPE_UINT64], true);

        if (!$supports_trends) {
            return [
                'points' => $this->fetchHistory($itemid, $value_type, $time_from, $time_till),
                'gapThresholdFloor' => self::HISTORY_GAP_FLOOR,
            ];
        }

        if ($seconds <= self::TREND_BLEND_SECONDS) {
            $points = $this->fetchHistory($itemid, $value_type, $time_from, $time_till);
            $used_trends = false;

            if ($points === []) {
                $points = $this->fetchTrends($itemid, $time_from, $time_till);
                $used_trends = true;
            }

            return [
                'points' => $points,
                'gapThresholdFloor' => $used_trends ? self::TREND_GAP_FLOOR : self::HISTORY_GAP_FLOOR,
            ];
        }

        // Trend rows can lag behind raw history by up to two hours, so keep the recent slice on history.
        $history_from = max($time_from, $time_till - self::RECENT_HISTORY_SECONDS);
        $trend_till = $history_from - 1;
        $history_points = $this->fetchHistory($itemid, $value_type, $history_from, $time_till);
        $trend_points = $trend_till >= $time_from
            ? $this->fetchTrends($itemid, $time_from, $trend_till)
            : [];

        if ($trend_points !== []) {
            return [
                'points' => array_values(array_merge($trend_points, $history_points)),
                'gapThresholdFloor' => self::TREND_GAP_FLOOR,
            ];
        }

        return [
            'points' => $this->fetchHistory($itemid, $value_type, $time_from, $time_till),
            'gapThresholdFloor' => self::HISTORY_GAP_FLOOR,
        ];
    }

    private function fetchHistory(
        string $itemid,
        int $value_type,
        int $time_from,
        int $time_till,
        string $sortorder = 'ASC',
        ?int $limit = null
    ): array {
        $params = [
            'output' => ['value', 'clock'],
            'history' => $value_type,
            'itemids' => [$itemid],
            'time_from' => $time_from,
            'time_till' => $time_till,
            'sortfield' => 'clock',
            'sortorder' => $sortorder
        ];

        if ($limit !== null) {
            $params['limit'] = $limit;
        }

        $records = API::History()->get($params);

        $points = array_map(static function(array $record): array {
            return [
                't' => (int) ($record['clock'] ?? 0),
                'v' => (float) ($record['value'] ?? 0),
            ];
        }, $records);

        if ($sortorder === 'DESC') {
            usort($points, static fn(array $left, array $right): int => $left['t'] <=> $right['t']);
        }

        return $points;
    }

    private function fetchTrends(string $itemid, int $time_from, int $time_till): array
    {
        $records = API::Trend()->get([
            'output' => ['value_avg', 'clock'],
            'itemids' => [$itemid],
            'time_from' => $time_from,
            'time_till' => $time_till,
        ]);

        // trend.get takes no sort parameters (7.x ignored them, 8.0 rejects them), so order the rows here.
        $points = array_map(static function(array $record): array {
            return [
                't' => (int) ($record['clock'] ?? 0),
                'v' => (float) ($record['value_avg'] ?? 0),
            ];
        }, $records);

        usort($points, static fn(array $left, array $right): int => $left['t'] <=> $right['t']);

        return $points;
    }

    private function applyMetricTransforms(array $points, array $request): array
    {
        if ($request['display_kind'] === 'percent' && $request['invert_percent'] === 1) {
            return array_map(static function(array $point): array {
                return [
                    't' => $point['t'],
                    'v' => 100 - $point['v'],
                ];
            }, $points);
        }

        return $points;
    }

    private function downsamplePoints(array $points): array
    {
        if (count($points) <= self::MAX_POINTS) {
            return $points;
        }

        $stride = count($points) / self::MAX_POINTS;
        $downsampled = [];

        for ($i = 0; $i < self::MAX_POINTS; $i++) {
            $downsampled[] = $points[(int) floor($i * $stride)];
        }

        $downsampled[] = $points[count($points) - 1];

        return $downsampled;
    }

    private function calculateBounds(array $points, array $request): array
    {
        if ($points === []) {
            return [
                $request['axis_min'] ?? 0,
                $request['axis_max'] ?? 0,
            ];
        }

        $min = INF;
        $max = -INF;

        foreach ($points as $point) {
            $value = (float) ($point['v'] ?? 0);
            $min = min($min, $value);
            $max = max($max, $value);
        }

        if ($request['axis_min'] !== null) {
            $min = $request['axis_min'];
        }

        if ($request['axis_max'] !== null && $request['axis_max'] > 0) {
            $max = $request['axis_max'];
        }

        return [
            is_finite($min) ? $min : 0,
            is_finite($max) ? $max : 0,
        ];
    }

    private function toItemRef(?array $metric): ?array
    {
        if ($metric === null || !array_key_exists('itemid', $metric) || !array_key_exists('value_type', $metric)) {
            return null;
        }

        return [
            'itemid' => (string) $metric['itemid'],
            'name' => $metric['name'] ?? null,
            'value_type' => (int) $metric['value_type'],
        ];
    }

    private function normalizeOptionalString(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $normalized = trim((string) $value);

        return $normalized === '' ? null : $normalized;
    }

    private function normalizeOptionalInt(mixed $value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (int) $value : null;
    }

    private function normalizeOptionalFloat(mixed $value): ?float
    {
        if ($value === null || $value === '') {
            return null;
        }

        return is_numeric($value) ? (float) $value : null;
    }

    private function normalizeBoolFlag(mixed $value): int
    {
        return (int) ((string) $value === '1');
    }
}
