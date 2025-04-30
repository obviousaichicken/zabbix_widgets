<?php declare(strict_types = 0);

/**
 * Eduard Bisschops
 * Licensed under the Apache License, Version 2.0.
 **/

namespace Modules\banner;

use Zabbix\Core\CWidget;

class Widget extends CWidget {

	public function getTranslationStrings(): array {
		return [
			'class.widget.js' => [
				'No data' => _('No data')
			]
		];
	}
}
