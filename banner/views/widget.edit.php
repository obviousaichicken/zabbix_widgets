<?php declare(strict_types = 0);

/**
 * Eduard Bisschops
 * Licensed under the Apache License, Version 2.0.
 **/

use Modules\banner\Includes\WidgetForm;

$form = new CWidgetFormView($data);

$form
	->addField(
		new CWidgetFieldTextBoxView($data['fields']['dp_title'])
	)
	->addField(
		new CWidgetFieldTextAreaView($data['fields']['dp_description'])
	)
	->addFieldset(
		(new CWidgetFormFieldsetCollapsibleView(_('Styling')))
			->addField(
				new CWidgetFieldSelectView($data['fields']['title_size'])
			)
			->addField(
				new CWidgetFieldSelectView($data['fields']['text_size'])
			)
			->addField(
				new CWidgetFieldColorView($data['fields']['title_color'])
			)
			->addField(
				new CWidgetFieldColorView($data['fields']['text_color'])
			)
			->addField(
				new CWidgetFieldSelectView($data['fields']['horizontal_align'])
			)
			->addField(
				new CWidgetFieldSelectView($data['fields']['vertical_align'])
			)
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