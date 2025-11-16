<?php

/*
 * MIT License
 * Copyright (c) 2025 ObviousAIChicken
 * github.com/obviousaichicken/zabbix_widgets
 */

namespace Modules\Banner;

use Zabbix\Core\CWidget;

class Widget extends CWidget
{

    // Font size options
    public const FONT_SIZE_OPTIONS = [
        0  => 'Default',
        1  => '14px',
        2  => '16px',
        3  => '18px',
        4  => '21px',
        5  => '24px',
        6  => '28px',
        7  => '32px',
        8  => '36px',
        9  => '42px',
        10 => '48px',
        11 => '56px',
        12 => '64px',
        13 => '72px',
        14 => '84px',
    ];

    // Horizontal alignment options
    public const HORIZONTAL_ALIGN_OPTIONS = [
        0 => 'Left',
        1 => 'Center',
        2 => 'Right',
    ];

    // Vertical alignment options
    public const VERTICAL_ALIGN_OPTIONS = [
        0 => 'Top',
        1 => 'Center',
        2 => 'Bottom',
    ];

    // Background display options
    public const BACKGROUND_DISPLAY_OPTIONS = [
        0 => 'Fit',
        1 => 'Stretch',
        2 => 'Padded',
    ];

    public function getTranslationStrings(): array
    {
        return [
            'class.widget.js' => [
                'No data' => _('No data'),
            ],
        ];
    }
}
