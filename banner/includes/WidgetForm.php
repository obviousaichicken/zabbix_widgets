<?php declare(strict_types = 0);

/**
 * Eduard Bisschops
 * Licensed under the Apache License, Version 2.0.
 **/

namespace Modules\banner\Includes;

use Modules\banner\Widget;

use Zabbix\Widgets\{
	CWidgetField,
	CWidgetForm
};

use Zabbix\Widgets\Fields\{
	CWidgetFieldCheckBox,
	CWidgetFieldColor,
	CWidgetFieldSelect,
	CWidgetFieldTextArea,
	CWidgetFieldTextBox
};

class WidgetForm extends CWidgetForm {

	public function addFields(): self {
		$font_sizes = [
			0 => 'Default',
			1 => '16px',
			2 => '18px',
			3 => '21px',
			4 => '24px',
			5 => '28px',
			6 => '32px',
			7 => '36px',
			8 => '42px',
			9 => '48px',
			10 => '56px',
			11 => '64px',
			12 => '72px',
			13 => '84px',
		];

		$horizontal_align_options = [
			0 => _('Left'),
			1 => _('Center'),
			2 => _('Right')
		];

		$vertical_align_options = [
			0 => _('Top'),
			1 => _('Center'),
			2 => _('Bottom')
		];

		$bg_display_options = [
			0 => _('Fit'),
			1 => _('Spread')
		];

		return $this
		->addField(
			(new CWidgetFieldTextBox('dp_title', _('Title')))
		)
		->addField(
			(new CWidgetFieldTextArea('dp_description', _('Description')))
		)
		->addField(
			(new CWidgetFieldSelect('title_size', _('Title size'), $font_sizes))
			->setDefault(0)
		)
		->addField(
			(new CWidgetFieldColor('title_color', _('Title color')))
		)
		->addField(
			(new CWidgetFieldColor('bg_color', _('Background color')))
		)
		->addField(
			(new CWidgetFieldTextBox('bg_image', _('Background image')))
		)
		->addField(
			(new CWidgetFieldSelect('bg_display', _('Background display'), $bg_display_options))
			->setDefault(0)
		)
		->addField(
			(new CWidgetFieldColor('text_color', _('Description color')))
		)
		->addField(
			(new CWidgetFieldSelect('text_size', _('Description size'), $font_sizes))
			->setDefault(0)
		)
		->addField(
			(new CWidgetFieldSelect('vertical_align', _('Vertical align'), $vertical_align_options))
			->setDefault(0)
		)
		->addField(
			(new CWidgetFieldSelect('horizontal_align', _('Horizontal align'), $horizontal_align_options))
			->setDefault(0)
		);
	}
}