<?php

/*
 * MIT License
 * Copyright (c) 2025 ObviousAIChicken
 * github.com/obviousaichicken/zabbix_widgets
 */

$view = new CWidgetView($data);

// Container
$container = (new CDiv())->setId('container');

// CPU
if ($data['config']['cpu_show']) {
    $container->addItem(
        (new CDiv())->addClass('row')->addItem([
            (new CTag('aside', true))->addClass('label')->addItem('Processor'),
            (new CDiv())->addClass('data')->addItem(
                (new CDiv())->addClass('bar')->addItem(
                    (new CDiv())->addClass('fill cpu')
                )
            ),
        ])
    );
}

// RAM
if ($data['config']['ram_show']) {
    $container->addItem(
        (new CDiv())->addClass('row')->addItem([
            (new CTag('aside', true))->addClass('label')->addItem('Memory'),
            (new CDiv())->addClass('data')->addItem(
                (new CDiv())->addClass('bar')->addItem(
                    (new CDiv())->addClass('fill ram')
                )
            ),
        ])
    );
}

// Load
if ($data['config']['load_show']) {
    $container->addItem(
        (new CDiv())->addClass('row')->addItem([
            (new CTag('aside', true))->addClass('label')->addItem('Load'),
            (new CDiv())->addClass('data')->addItem(
                (new CDiv())->addClass('bar')->addItem(
                    (new CDiv())->addClass('fill load')
                )
            ),
        ])
    );
}

// Interfaces
if (! empty($data['config']['interfaces_show'])) {
    $row       = (new CDiv())->addClass('row');
    $label     = (new CTag('aside', true))->addClass('label')->addItem('Interfaces');
    $data_cell = (new CDiv())->addClass('data interfaces-data multi');

    foreach (($data['interfaces'] ?? []) as $name => $vals) {
        $data_cell->addItem(
            (new CDiv())
                ->addClass('cell')
                ->setAttribute('data-key', $name)
                ->addItem((new CDiv())->addClass('bar')->addItem((new CDiv())->addClass('fill')))
                ->addItem((new CTag('span'))->addClass('text')->addItem($name . ' -'))
        );
    }

    $row->addItem($label)->addItem($data_cell);
    $container->addItem($row);
}

// Disks
if (! empty($data['config']['disks_show'])) {
    $row       = (new CDiv())->addClass('row');
    $label     = (new CTag('aside', true))->addClass('label')->addItem('Disk util.');
    $data_cell = (new CDiv())->addClass('data disks-data multi');

    foreach (($data['disks'] ?? []) as $r) {
        $data_cell->addItem(
            (new CDiv())
                ->addClass('cell')
                ->setAttribute('data-key', $r['name'])
                ->addItem((new CDiv())->addClass('bar')->addItem((new CDiv())->addClass('fill')))
                ->addItem((new CTag('span'))->addClass('text'))
        );
    }

    $row->addItem($label)->addItem($data_cell);
    $container->addItem($row);
}

// Partitions section
if (! empty($data['config']['partitions_show'])) {
    $row       = (new CDiv())->addClass('row');
    $label     = (new CTag('aside', true))->addClass('label')->addItem('Partitions');
    $data_cell = (new CDiv())->addClass('data partitions-data multi');

    foreach (($data['partitions'] ?? []) as $r) {
        $data_cell->addItem(
            (new CDiv())
                ->addClass('cell')
                ->setAttribute('data-key', $r['name'])
                ->addItem((new CDiv())->addClass('bar')->addItem((new CDiv())->addClass('fill')))
                ->addItem((new CTag('span'))->addClass('text'))
        );
    }

    $row->addItem($label)->addItem($data_cell);
    $container->addItem($row);
}

$view
    ->addItem($container)
    ->setVar('cpu', $data['cpu'] ?? null)
    ->setVar('ram', $data['ram'] ?? null)
    ->setVar('load', $data['load'] ?? null)
    ->setVar('load_percent', $data['load_percent'] ?? null)
    ->setVar('interfaces', $data['interfaces'] ?? [])
    ->setVar('disks', $data['disks'] ?? [])
    ->setVar('partitions', $data['partitions'] ?? [])
    ->setVar('config', $data['config'])
    ->show();
