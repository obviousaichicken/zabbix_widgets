<?php

/*
 * MIT License
 * Copyright (c) 2025 ObviousAIChicken
 * github.com/obviousaichicken/zabbix_widgets
 */

// Backwards compatibility
// The ZBX_STYLE_COLOR_PICKER constant disappeared in Zabbix 7.4
$color_picker_class = defined('ZBX_STYLE_COLOR_PICKER') ? ZBX_STYLE_COLOR_PICKER : null;

$form = new CWidgetFormView($data);

$form
    ->addField(
        new CWidgetFieldMultiSelectHostView($data['fields']['hostid'])
    )
    ->addField(
        new CWidgetFieldCheckBoxView($data['fields']['cpu_show'])
    )
    ->addField(
        new CWidgetFieldCheckBoxView($data['fields']['ram_show'])
    )
    ->addField(
        new CWidgetFieldCheckBoxView($data['fields']['load_show'])
    )
    ->addField(
        new CWidgetFieldIntegerBoxView($data['fields']['load_high'])
    )
    ->addField(
        new CWidgetFieldCheckBoxView($data['fields']['interfaces_show'])
    )
    ->addItem(getInterfaceHighViews($form, $data['fields']))

    ->addField(
        new CWidgetFieldCheckBoxView($data['fields']['disks_show'])
    )
    ->addField(
        new CWidgetFieldCheckBoxView($data['fields']['partitions_show'])
    )
    ->addField(
        new CWidgetFieldRadioButtonListView($data['fields']['color_scheme'])
    )
    ->addFieldset(
        (new CWidgetFormFieldsetCollapsibleView(_('Solid')))
            ->addField(
                new CWidgetFieldColorView($data['fields']['fill_color'])
            )
    )
    ->addFieldset(
        (new CWidgetFormFieldsetCollapsibleView(_('Threshold')))
            ->addItem(getThresholdHighViews($form, $data['fields']))
            ->addItem(getThresholdMediumViews($form, $data['fields']))
            ->addField(
                new CWidgetFieldColorView($data['fields']['th_color_3'])
            )
    )
    ->includeJsFile('widget.edit.js')
    ->addJavaScript('form.init(' . json_encode([
        'color_picker_class' => $color_picker_class,
    ], JSON_THROW_ON_ERROR) . ');')
    ->show();

function getInterfaceHighViews(CWidgetFormView $form, array $fields): array
{
    $interfaces_unit = $form->registerField(
        new CWidgetFieldRadioButtonListView($fields['interfaces_unit'])
    );
    $interfaces_high = $form->registerField(new CWidgetFieldIntegerBoxView($fields['interfaces_high']));

    return [
        new CLabel(_('Interfaces High'), 'interfaces_unit'),
        new CFormField(new CHorList([
            $interfaces_high->getView(),
            $interfaces_unit->getView(),
        ])),
    ];
}

function getThresholdHighViews(CWidgetFormView $form, array $fields): array
{
    $th_num_1 = $form->registerField(
        new CWidgetFieldIntegerBoxView($fields['th_num_1'])
    );
    $th_color_1 = $form->registerField(
        new CWidgetFieldColorView($fields['th_color_1'])
    );

    return [
        new CLabel(_('High'), 'th_num_1'),
        new CFormField(new CHorList([
            $th_num_1->getView(),
            $th_color_1->getView(),
        ])),
    ];
}

function getThresholdMediumViews(CWidgetFormView $form, array $fields): array
{
    $th_num_2 = $form->registerField(
        new CWidgetFieldIntegerBoxView($fields['th_num_2'])
    );
    $th_color_2 = $form->registerField(
        new CWidgetFieldColorView($fields['th_color_2'])
    );

    return [
        new CLabel(_('Medium'), 'th_num_2'),
        new CFormField(new CHorList([
            $th_num_2->getView(),
            $th_color_2->getView(),
        ])),
    ];
}
