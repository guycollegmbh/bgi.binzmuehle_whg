<?php

declare(strict_types=1);

use Contao\CoreBundle\DataContainer\PaletteManipulator;

PaletteManipulator::create()
    ->addField('jumpTo', 'title_legend', PaletteManipulator::POSITION_APPEND)
    ->addField('hide_bewerben', 'title_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('apartments_list', 'tl_module');

PaletteManipulator::create()
    ->addField('hide_bewerben', 'title_legend', PaletteManipulator::POSITION_APPEND)
    ->applyToPalette('apartments_detail', 'tl_module');

$GLOBALS['TL_DCA']['tl_module']['fields']['hide_bewerben'] = [
    'inputType' => 'checkbox',
    'eval' => ['tl_class' => 'w50 m12'],
    'sql' => ['type' => 'boolean', 'default' => false],
];
