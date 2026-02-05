<?php

declare(strict_types=1);

use Contao\CoreBundle\DataContainer\PaletteManipulator;

$GLOBALS['TL_FFL']['apartment_select'] = \Guycollegmbh\ApartmentsBundle\Widget\FormApartmentSelect::class;

$GLOBALS['TL_DCA']['tl_form_field']['palettes']['apartment_select'] = '{type_legend},type,name,label;{apartment_legend},apartment_bezeichnung;{expert_legend:hide},class,accesskey;{template_legend:hide},customTpl;{invisible_legend:hide},invisible';

$GLOBALS['TL_DCA']['tl_form_field']['fields']['apartment_bezeichnung'] = [
    'label'     => ['Objektbezeichnung', 'Geben Sie die Bezeichnung ein, z.B. Parkplatz, Keller, Atelier'],
    'inputType' => 'text',
    'eval'      => ['tl_class' => 'w50', 'maxlength' => 255],
    'sql'       => ['type' => 'string', 'length' => 255, 'default' => 'Parkplatz'],
];
