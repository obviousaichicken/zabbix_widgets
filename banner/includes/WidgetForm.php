<?php

/**
 * MIT License
 * Copyright (c) 2025 ObviousAIChicken
 * github.com/obviousaichicken/zabbix_widgets
 **/

declare(strict_types = 0);

namespace Modules\banner\Includes;

use Modules\banner\Widget;

use Zabbix\Widgets\{
	CWidgetField,
	CWidgetForm
};

use Zabbix\Widgets\Fields\{
	CWidgetFieldCheckBox,
	CWidgetFieldCheckBoxList,
	CWidgetFieldColor,
	CWidgetFieldRadioButtonList,
	CWidgetFieldSelect,
	CWidgetFieldTextArea,
	CWidgetFieldTextBox
};

class WidgetForm extends CWidgetForm {

	public function addFields(): self {
		return $this
		->addField(
			(new CWidgetFieldTextBox('title', _('Title')))
		)
		->addField(
			(new CWidgetFieldTextArea('description', _('Description')))
		)
		->addField(
			(new CWidgetFieldRadioButtonList('horizontal_align', _('Horizontal align'), Widget::HORIZONTAL_ALIGN_OPTIONS))
			->setDefault(0)
		)
		->addField(
			new CWidgetFieldCheckBoxList('icon_mode', _('Header mode'), [
				1 => ' (Description and scrollbar will be hidden and the title will be vertically centered.)'
			])
		)
		->addField(
			(new CWidgetFieldCheckBox('title_bold', _('Bold')))
			->setDefault(0)
		)
		->addField(
			(new CWidgetFieldSelect('title_size', _('Size'), Widget::FONT_SIZE_OPTIONS))
			->setDefault(0)
		)
		->addField(
			(new CWidgetFieldColor('title_color', _('Color')))
		)
		->addField(
			(new CWidgetFieldCheckBox('description_bold', _('Bold')))
			->setDefault(0)
		)
		->addField(
			(new CWidgetFieldSelect('description_size', _('Size'), Widget::FONT_SIZE_OPTIONS))
			->setDefault(0)
		)
		->addField(
			(new CWidgetFieldColor('description_color', _('Color')))
		)
		->addField(
			(new CWidgetFieldColor('bg_color', _('Color')))
		)
		->addField(
			(new CWidgetFieldTextBox('bg_image', _('Image')))
		)
		->addField(
			(new CWidgetFieldSelect('bg_display', _('Display'), Widget::BACKGROUND_DISPLAY_OPTIONS))
			->setDefault(0)
		);
	}
}
