<?php

/**
 * MIT License
 * Copyright (c) 2025 ObviousAIChicken
 * github.com/obviousaichicken/zabbix_widgets
 **/

declare(strict_types = 0);

use Modules\banner\Includes\WidgetForm;

$form = new CWidgetFormView($data);

$form
	->addField(
		new CWidgetFieldTextBoxView($data['fields']['title'])
	)
	->addField(
		new CWidgetFieldTextAreaView($data['fields']['description'])
	)
	->addField(
		new CWidgetFieldRadioButtonListView($data['fields']['horizontal_align'])
	)
	->addField(
		new CWidgetFieldCheckBoxListView($data['fields']['icon_mode'])
	)
	->addFieldset(
		(new CWidgetFormFieldsetCollapsibleView(_('Title')))
			->addField(
				new CWidgetFieldCheckBoxView($data['fields']['title_bold'])
			)
			->addField(
				new CWidgetFieldSelectView($data['fields']['title_size'])
			)
			->addField(
				new CWidgetFieldColorView($data['fields']['title_color'])
			)
	)
	->addFieldset(
		(new CWidgetFormFieldsetCollapsibleView(_('Description')))
			->addField(
				new CWidgetFieldCheckBoxView($data['fields']['description_bold'])
			)
			->addField(
				new CWidgetFieldSelectView($data['fields']['description_size'])
			)
			->addField(
				new CWidgetFieldColorView($data['fields']['description_color'])
			)
	)
	->addFieldset(
		(new CWidgetFormFieldsetCollapsibleView(_('Background')))
			->addField(
				new CWidgetFieldColorView($data['fields']['bg_color'])
			)
			->addField(
				new CWidgetFieldTextBoxView($data['fields']['bg_image'])
			)
			->addField(
				new CWidgetFieldSelectView($data['fields']['bg_display'])
			)
	)
	->includeJsFile('widget.edit.js.php')
	->addJavaScript('widget_banner_form.init();')
	->show();
