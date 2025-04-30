<?php declare(strict_types = 1);

/**
 * Eduard Bisschops
 * Licensed under the Apache License, Version 2.0.
 **/

use Modules\banner\Widget;
use Modules\banner\Includes\WidgetForm;

// --- Start of relevant code ---

// Get the widget field values
$fields = $data['fields_values'];

// Get default styles for common use
$bg_color = !empty($fields['bg_color']) ? $fields['bg_color'] : 'FFFFFF';
$text_color = !empty($fields['text_color']) ? $fields['text_color'] : '000000';
$title_color = !empty($fields['title_color']) ? $fields['title_color'] : $text_color;
$bg_image = !empty($fields['bg_image']) ? $fields['bg_image'] : '';
$bg_display = isset($fields['bg_display']) ? (int)$fields['bg_display'] : 0;

// Map horizontal alignment to text-align value
$horizontal_align_map = [
    0 => 'left',
    1 => 'center',
    2 => 'right'
];

// Set text alignment based on horizontal alignment
$horizontal_align_value = isset($fields['horizontal_align']) ? (int)$fields['horizontal_align'] : 0;
$text_align = isset($horizontal_align_map[$horizontal_align_value])
    ? $horizontal_align_map[$horizontal_align_value]
    : 'left';
$text_align_style = "text-align: {$text_align};";

// Map font size values to actual css values
$font_size_map = [
    0 => '', 1 => '16px', 2 => '18px', 3 => '21px', 4 => '24px', 5 => '28px',
    6 => '32px', 7 => '36px', 8 => '42px', 9 => '48px', 10 => '56px',
    11 => '64px', 12 => '72px', 13 => '84px',
];

// Get font size styles
$text_size_style = isset($font_size_map[$fields['text_size']]) && $fields['text_size'] != 0
    ? 'font-size: '.$font_size_map[$fields['text_size']].';'
    : '';
$title_size_style = isset($font_size_map[$fields['title_size']]) && $fields['title_size'] != 0
    ? 'font-size: '.$font_size_map[$fields['title_size']].';'
    : '';

// Check if title and description are not empty
$has_title = !empty($fields['dp_title']);
$has_description = !empty($fields['dp_description']);

// --- Widget Container Styling ---
// This container's job is to center the content_block inside it.
$widget_style = [
    "background-color: #{$bg_color};",
    "color: #{$text_color};",
    "padding: 15px;",
    "height: 100%;",
    "width: 100%;",
    "box-sizing: border-box;",
    "display: flex;"
];

// Add background image if provided
if (!empty($bg_image)) {
    $widget_style[] = "background-image: url('{$bg_image}');";

    // Set background size based on display option
    if ($bg_display == 1) { // Spread
        $widget_style[] = "background-size: cover;";
    } else {
        $widget_style[] = "background-size: contain;";
        $widget_style[] = "background-origin: content-box;";
        $widget_style[] = "background-clip: content-box;";
    }

    $widget_style[] = "background-position: center;";
    $widget_style[] = "background-repeat: no-repeat;";

}

// Set horizontal alignment using numeric values
$horizontal_align_value = isset($fields['horizontal_align']) ? (int)$fields['horizontal_align'] : 0;
switch ($horizontal_align_value) {
    case 0: // Left
        $widget_style[] = "justify-content: flex-start;";
        break;
    case 1: // Center
        $widget_style[] = "justify-content: center;";
        break;
    case 2: // Right
        $widget_style[] = "justify-content: flex-end;";
        break;
    default:
        $widget_style[] = "justify-content: flex-start;";
        break;
}

// Set vertical alignment using numeric values
$vertical_align_value = isset($fields['vertical_align']) ? (int)$fields['vertical_align'] : 0;
switch ($vertical_align_value) {
    case 0: // Top
        $widget_style[] = "align-items: flex-start;";
        break;
    case 1: // Center
        $widget_style[] = "align-items: center;";
        break;
    case 2: // Bottom
        $widget_style[] = "align-items: flex-end;";
        break;
    default:
        $widget_style[] = "align-items: flex-start;";
        break;
}

// Create the main widget container
$widget = (new CDiv())
    ->addClass('banner-widget')
    ->setAttribute('style', implode(' ', $widget_style));

// --- Content Block ---
// This container holds the title and description together.
// It doesn't need special flex properties itself; it will be centered by the parent.
$content_block = (new CDiv())
    ->addClass('banner-content-block')
    // Optional: Add max-width to prevent very long text from breaking layout badly
    ->setAttribute('style', 'max-width: 100%;');

// --- Add Title to Content Block ---
if ($has_title) {
    $title_style_array = [
        "color: #{$title_color};",
        $title_size_style,
        $text_align_style,
        "line-height: 1;"
    ];
    // Add margin-bottom ONLY if description also exists
    if ($has_description) {
        $title_style_array[] = "margin-bottom: 10px;";
    }

    $content_block->addItem(
        (new CDiv($fields['dp_title']))
            ->addClass('banner-title')
            ->setAttribute('style', implode(' ', array_filter($title_style_array)))
    );
}

// --- Add Description to Content Block ---
if ($has_description) {
    $description_style_array = [
        // Inherits color from $widget unless overridden
        $text_size_style,
        $text_align_style,
        "white-space: pre-wrap;"
    ];

    $description_content = $fields['dp_description'];

    $content_block->addItem(
        (new CDiv($description_content))
            ->addClass('banner-description')
            ->setAttribute('style', implode(' ', array_filter($description_style_array)))
    );
}

// Add the content block (containing title and/or description) to the main widget
if ($has_title || $has_description) {
    $widget->addItem($content_block);
}

// Create the widget view and show it
$view = new CWidgetView($data);
$view->addItem($widget)->show();