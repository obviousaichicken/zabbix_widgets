<?php

/*
** Copyright (C) 2001-2025 Zabbix SIA
**
** This program is free software: you can redistribute it and/or modify it under the terms of
** the GNU Affero General Public License as published by the Free Software Foundation, version 3.
**
** This program is distributed in the hope that it will be useful, but WITHOUT ANY WARRANTY;
** without even the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
** See the GNU Affero General Public License for more details.
**
** You should have received a copy of the GNU Affero General Public License along with this program.
** If not, see <https://www.gnu.org/licenses/>.
**/

declare(strict_types = 0);

use Modules\banner\Widget;

?>

window.widget_banner_form = new class {

	init() {
		// Initialize all color pickers
		for (const colorpicker of jQuery('.<?= ZBX_STYLE_COLOR_PICKER ?> input')) {
			jQuery(colorpicker).colorpicker();
		}

		const overlay = overlays_stack.getById('widget_properties');

		for (const event of ['overlay.reload', 'overlay.close']) {
			overlay.$dialogue[0].addEventListener(event, () => { jQuery.colorpicker('hide'); });
		}
	}
};
