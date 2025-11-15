<?php

/*
 * MIT License
 * Copyright (c) 2025 ObviousAIChicken
 * github.com/obviousaichicken/zabbix_widgets
 */

namespace Modules\HostOverview\Includes;

use Zabbix\Widgets\CWidgetField;
use Zabbix\Widgets\CWidgetForm;
use Zabbix\Widgets\Fields\CWidgetFieldCheckBox;
use Zabbix\Widgets\Fields\CWidgetFieldColor;
use Zabbix\Widgets\Fields\CWidgetFieldIntegerBox;
use Zabbix\Widgets\Fields\CWidgetFieldMultiSelectHost;
use Zabbix\Widgets\Fields\CWidgetFieldRadioButtonList;

class WidgetForm extends CWidgetForm
{
    public const COLOR_SCHEME_THRESHOLD = 0;
    public const COLOR_SCHEME_SOLID     = 1;

    public const INTERFACES_UNIT_KBPS = 0;
    public const INTERFACES_UNIT_MBPS = 1;
    public const INTERFACES_UNIT_GBPS = 2;

    public const DEFAULT_COLOR_FILL             = '458ADC';
    public const DEFAULT_COLOR_THRESHOLD_HIGH   = 'FF4136';
    public const DEFAULT_COLOR_THRESHOLD_MEDIUM = 'FF851B';
    public const DEFAULT_COLOR_THRESHOLD_LOW    = '4C9F38';

    public const DEFAULT_THRESHOLD_HIGH   = 85;
    public const DEFAULT_THRESHOLD_MEDIUM = 70;

    public const DEFAULT_LOAD_HIGH       = 2;
    public const DEFAULT_INTERFACES_HIGH = 1;

    public function addFields(): self
    {
        return $this
            ->addField(
                (new CWidgetFieldMultiSelectHost('hostid', _('Host')))
                    ->setFlags(CWidgetField::FLAG_NOT_EMPTY | CWidgetField::FLAG_LABEL_ASTERISK)
                    ->setMultiple(false)
            )
            ->addField(
                (new CWidgetFieldColor('fill_color', _('Color')))
                    ->setDefault(self::DEFAULT_COLOR_FILL)
            )
            ->addField(
                (new CWidgetFieldColor('th_color_1', null))
                    ->setDefault(self::DEFAULT_COLOR_THRESHOLD_HIGH)
            )
            ->addField(
                (new CWidgetFieldColor('th_color_2', null))
                    ->setDefault(self::DEFAULT_COLOR_THRESHOLD_MEDIUM)
            )
            ->addField(
                (new CWidgetFieldColor('th_color_3', _('Regular')))
                    ->setDefault(self::DEFAULT_COLOR_THRESHOLD_LOW)
            )
            ->addField(
                (new CWidgetFieldIntegerBox('th_num_1', null))
                    ->setDefault(self::DEFAULT_THRESHOLD_HIGH)
            )
            ->addField(
                (new CWidgetFieldIntegerBox('th_num_2', null))
                    ->setDefault(self::DEFAULT_THRESHOLD_MEDIUM)
            )
            ->addField(
                (new CWidgetFieldCheckBox('cpu_show', _('Show Processor')))
                    ->setDefault(1)
            )
            ->addField(
                (new CWidgetFieldCheckBox('ram_show', _('Show Memory')))
                    ->setDefault(1)
            )
            ->addField(
                (new CWidgetFieldCheckBox('load_show', _('Show Load')))
                    ->setDefault(1)
            )
            ->addField(
                (new CWidgetFieldIntegerBox('load_high', _('Load High')))
                    ->setDefault(self::DEFAULT_LOAD_HIGH)
            )
            ->addField(
                (new CWidgetFieldCheckBox('interfaces_show', _('Show Interfaces')))
                    ->setDefault(1)
            )
            ->addField(
                (new CWidgetFieldIntegerBox('interfaces_high', _('Interfaces High')))
                    ->setDefault(self::DEFAULT_INTERFACES_HIGH)
            )
            ->addField(
                (new CWidgetFieldRadioButtonList('interfaces_unit', null, [
                    self::INTERFACES_UNIT_KBPS => _('Kbps'),
                    self::INTERFACES_UNIT_MBPS => _('Mbps'),
                    self::INTERFACES_UNIT_GBPS => _('Gbps'),
                ]))
                    ->setDefault(self::INTERFACES_UNIT_GBPS)
            )
            ->addField(
                (new CWidgetFieldCheckBox('disks_show', _('Show Disks')))
                    ->setDefault(1)
            )
            ->addField(
                (new CWidgetFieldRadioButtonList('color_scheme', _('Color Scheme'), [
                    self::COLOR_SCHEME_SOLID     => _('Solid'),
                    self::COLOR_SCHEME_THRESHOLD => _('Threshold'),
                ]))
                    ->setDefault(self::COLOR_SCHEME_SOLID)
            )
            ->addField(
                (new CWidgetFieldCheckBox('partitions_show', _('Show Partitions')))
                    ->setDefault(1)
            );
    }
}
