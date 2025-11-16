<?php

/*
 * MIT License
 * Copyright (c) 2025 ObviousAIChicken
 * github.com/obviousaichicken/zabbix_widgets
 */

namespace Modules\Banner\Actions;

use CControllerDashboardWidgetView;
use CControllerResponseData;

class WidgetView extends CControllerDashboardWidgetView
{
    protected function doAction(): void
    {
        // Pass the configured values to the view
        $this->setResponse(new CControllerResponseData([
            'name'          => $this->getInput('name', $this->widget->getName()),
            'fields_values' => $this->fields_values,
            'user'          => [
                'debug_mode' => $this->getDebugMode(),
            ],
        ]));
    }
}
