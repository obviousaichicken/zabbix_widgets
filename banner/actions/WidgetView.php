<?php declare(strict_types=0);

/**
 * Eduard Bisschops
 * Licensed under the Apache License, Version 2.0.
 **/

namespace Modules\banner\Actions;

use API, CControllerDashboardWidgetView, CControllerResponseData;

class WidgetView extends CControllerDashboardWidgetView {

	protected function doAction(): void {
		// Pass the configured values to the view
			$this->setResponse(new CControllerResponseData([
				'name' => $this->getInput('name', $this->widget->getName()),
				'fields_values' => $this->fields_values,
				'user' => [
					'debug_mode' => $this->getDebugMode()
				]
			]));
	}
}