<?php

/*
 * MIT License
 * Copyright (c) 2025 ObviousAIChicken
 * github.com/obviousaichicken/zabbix_widgets
 */

use Modules\Banner\Widget;

// Initialize style arrays for widget elements
$widget_style      = [];
$title_style       = [];
$description_style = [];

// Widget settings
$settings = $data['fields_values'];

// Content visibility flags
$has_title       = ! empty($settings['title']);
$has_description = ! empty($settings['description']);
$has_bg_image    = ! empty($settings['bg_image']);

// Set background color
$widget_style[] = "background-color: #{$settings['bg_color']};";

// Set special style if icon mode is enabled
if ($settings['icon_mode']) {
    $widget_style[] = "align-items: center;";
    $widget_style[] = "display: flex;";
    $widget_style[] = "overflow: hidden;";
    $widget_style[] = "width: 100%;";
}

// Background image rendering
if ($has_bg_image) {
    // Set background image
    $widget_style[] = "background-image: url('{$settings['bg_image']}');";

    // Set background size based on display option
    switch ($settings['bg_display']) {
        case 0: // Fit
            $widget_style[] = "background-size: contain;";
            break;
        case 1: // Stretch
            $widget_style[] = "background-size: cover;";
            break;
        case 2: // Padded
            $widget_style[] = "background-size: contain;";
            $widget_style[] = "background-origin: content-box;";
            $widget_style[] = "background-clip: content-box;";
            break;
    }
}

// Transform horizontal alignment value to css
switch ($settings['horizontal_align']) {
    case 1:
        $settings['horizontal_align'] = 'text-align: center;';
        break;
    case 2:
        $settings['horizontal_align'] = 'text-align: end;';
        break;
}

// Set horizontal content alignment
$widget_style[] = $settings['horizontal_align'];

// Create the widget component
$widget = (new CDiv())
    ->addClass('banner')
    ->setAttribute('style', implode(' ', $widget_style));

// Title rendering
if ($has_title) {
    // Set font color
    $title_style[] = "color: #{$settings['title_color']};";

    // Set font size
    $title_style[] = 'font-size: ' . formatFontSize($settings['title_size']) . ';';

    // Set boldness
    if ($settings['title_bold']) {
        $title_style[] = "font-weight: bold;";
    }

    // Format the bbcode
    $formatted_title = formatBBCodes($settings['title']);

    // Create the title component
    $title = (new CDiv())
        ->addClass('title')
        ->setAttribute('style', implode(' ', array_filter($title_style)))
        ->addItem(
            (new CJsScript())->addItem($formatted_title)
        );

    // Add the title component to the widget component
    $widget->addItem($title);
}

// Description rendering (if not in icon mode)
if ($has_description && ! $settings['icon_mode']) {
    // Set font color
    $description_style[] = "color: #{$settings['description_color']};";

    // Set font size
    $description_style[] = 'font-size: ' . formatFontSize($settings['description_size']) . ';';

    // Set boldness
    if ($settings['description_bold']) {
        $description_style[] = "font-weight: bold;";
    }

    // Format the bbcode
    $formatted_description = formatBBCodes($settings['description']);

    // Create the description component
    $description = (new CDiv())
        ->addClass('description')
        ->setAttribute('style', implode(' ', array_filter($description_style)))
        ->addItem(
            (new CJsScript())->addItem($formatted_description)
        );

    // Add the description to the widget
    $widget->addItem($description);
}

// Create a view and add the widget component
$view = new CWidgetView($data);
$view->addItem($widget)->show();

/**
 * Converts BBCode markup to HTML
 *
 * @param string $text Input text containing BBCode markup
 * @return string HTML formatted text
 */
function formatBBCodes($text)
{
    // Bold
    $text = preg_replace('/\[b\](.*?)\[\/b\]/is', '<b>$1</b>', $text);

    // Underline
    $text = preg_replace('/\[u\](.*?)\[\/u\]/is', '<u>$1</u>', $text);

    // Italic
    $text = preg_replace('/\[i\](.*?)\[\/i\]/is', '<i>$1</i>', $text);

    // Strike
    $text = preg_replace('/\[s\](.*?)\[\/s\]/is', '<s>$1</s>', $text);

    // Color
    $text = preg_replace('/\[color=([^\]]+)\](.*?)\[\/color\]/is', '<span style="color: $1;">$2</span>', $text);

    // Image
    $text = preg_replace('/\[img\](.*?)\[\/img\]/is', '<img src="$1">', $text);

    // Link
    $text = preg_replace('/\[link=([^\]]+)\](.*?)\[\/link\]/is', '<a href="$1" target="_blank">$2</a>', $text);

    // Center
    $text = preg_replace('/\[center\](.*?)\[\/center\]/is', '<div style="text-align: center;">$1</div>', $text);

    return $text;
}

/**
 * Converts numeric size index to appropriate CSS font size value
 *
 * @param int $sizeIndex Index from the font size options array
 * @return string CSS font-size value or empty string for default size
 */
function formatFontSize($sizeIndex)
{
    // Get the font sizes
    $font_sizes = Widget::FONT_SIZE_OPTIONS;

    // Return empty string for default size
    if ($font_sizes[$sizeIndex] == "Default") {
        return '';
    }

    return $font_sizes[$sizeIndex];
}
