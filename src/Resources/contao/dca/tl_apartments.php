<?php

declare(strict_types=1);

/*
 * This file is part of Apartments Bundle.
 *
 * (c) GUYCOLLE GMBH / Patrick Grob
 *
 * @license LGPL-3.0-or-later
 */

use Contao\Config;
use Contao\DataContainer;
use Contao\DC_Table;

/**
 * Table tl_apartments
 */
$GLOBALS['TL_DCA']['tl_apartments'] = [
    'config' => [
        'dataContainer' => DC_Table::class,
        'enableVersioning' => true,
        'switchToEdit' => true,
        'sql' => [
            'keys' => [
                'id' => 'primary',
                'tstamp' => 'index',
            ],
        ],
    ],
    'list' => [
        'sorting' => [
            'mode' => DataContainer::MODE_SORTED,
            'fields' => ['objektnummer'],
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout' => 'search,limit',
        ],
        'label' => [
            'fields' => ['objektnummer'],
            'format' => '%s',
        ],
        'operations' => [
            'edit',
            'copy',
            'delete',
            'show',
            'toggle' => [
                'href' => 'act=toggle&amp;field=published',
                'icon' => 'visible.svg',
            ],
        ],
    ],
    'palettes' => [
        'default' => '{beschreibung_legend},objektnummer,bezeichnung,bauetappe,zeile,adresse,etage,zimmer,flaeche;{detailseite_legend},nettomietzins,nebenkosten,bruttomietzins;{links_legend},grundriss;{published_legend},published',
    ],
    'fields' => [
        'id' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'autoincrement' => true],
        ],
        'tstamp' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
        ],
        'objektnummer' => [
            'label' => &$GLOBALS['TL_LANG']['tl_apartments']['Objektnummer'],
            'search' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50', 'maxlength' => 255, 'mandatory' => true],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
        ],
        'bezeichnung' => [
            'label' => &$GLOBALS['TL_LANG']['tl_apartments']['bezeichnung'],
            'search' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50', 'maxlength' => 255, 'mandatory' => true],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
        ],
        'bauetappe' => [
            'label' => &$GLOBALS['TL_LANG']['tl_apartments']['bauetappe'],
            'search' => true,
            'inputType' => 'checkbox',
            'eval' => [
                'mandatory' => false,
                'fieldType' => 'checkbox',
                'includeBlankOption' => true,
                'tl_class' => 'w50 clr m12',
                'multiple' => true,
            ],
            'options_callback' => ['tl_apartments', 'getAngebotstypen'],
            'sql' => 'blob NULL',
        ],
        'zeile' => [
            'label' => &$GLOBALS['TL_LANG']['tl_apartments']['zeile'],
            'search' => true,
            'inputType' => 'checkbox',
            'eval' => [
                'mandatory' => false,
                'fieldType' => 'checkbox',
                'includeBlankOption' => true,
                'tl_class' => 'w50 m12',
                'multiple' => true,
            ],
            'options_callback' => ['tl_apartments', 'getAngebotstypen'],
            'sql' => 'blob NULL',
        ],
        'adresse' => [
            'label' => &$GLOBALS['TL_LANG']['tl_apartments']['adresse'],
            'search' => true,
            'inputType' => 'checkbox',
            'eval' => [
                'mandatory' => false,
                'fieldType' => 'checkbox',
                'includeBlankOption' => true,
                'tl_class' => 'w50 clr m12',
                'multiple' => true,
            ],
            'options_callback' => ['tl_apartments', 'getAngebotstypen'],
            'sql' => 'blob NULL',
        ],
        'etage' => [
            'label' => &$GLOBALS['TL_LANG']['tl_apartments']['etage'],
            'search' => true,
            'inputType' => 'checkbox',
            'eval' => [
                'mandatory' => false,
                'fieldType' => 'checkbox',
                'includeBlankOption' => true,
                'tl_class' => 'w50 m12',
                'multiple' => true,
            ],
            'options_callback' => ['tl_apartments', 'getAngebotstypen'],
            'sql' => 'blob NULL',
        ],
        'zimmer' => [
            'label' => &$GLOBALS['TL_LANG']['tl_apartments']['zimmer'],
            'search' => true,
            'inputType' => 'checkbox',
            'eval' => [
                'mandatory' => false,
                'fieldType' => 'checkbox',
                'includeBlankOption' => true,
                'tl_class' => 'w50 m12',
                'multiple' => true,
            ],
            'options_callback' => ['tl_apartments', 'getAngebotstypen'],
            'sql' => 'blob NULL',
        ],
        'flaeche' => [
            'label' => &$GLOBALS['TL_LANG']['tl_apartments']['flaeche'],
            'search' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50', 'maxlength' => 255, 'mandatory' => true],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
        ],
        'nettomietzins' => [
            'label' => &$GLOBALS['TL_LANG']['tl_apartments']['nettomietzins'],
            'search' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50', 'maxlength' => 255, 'mandatory' => true],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
        ],
        'nebenkosten' => [
            'label' => &$GLOBALS['TL_LANG']['tl_apartments']['nettomietzins'],
            'search' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50', 'maxlength' => 255, 'mandatory' => true],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
        ],
        'bruttomietzins' => [
            'label' => &$GLOBALS['TL_LANG']['tl_apartments']['bruttomietzins'],
            'search' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50', 'maxlength' => 255, 'mandatory' => true],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
        ],
        'grundriss' => [
            'label' => &$GLOBALS['TL_LANG']['tl_apartments']['grundriss'],
            'inputType' => 'fileTree',
            'eval' => [
                'fieldType' => 'radio',
                'filesOnly' => true,
                'extensions' => Config::get('validImageTypes'),
                'mandatory' => true,
                'tl_class' => 'w100',
            ],
            'sql' => [
                'type' => 'binary',
                'length' => 16,
                'notnull' => false,
                'fixed' => true,
            ],
        ],
        'published' => [
            'label' => &$GLOBALS['TL_LANG']['tl_apartments']['published'],
            'inputType' => 'checkbox',
            'toggle' => true,
            'eval' => ['tl_class' => 'clr'],
            'sql' => [
                'type' => 'boolean',
                'default' => false,
            ],
        ],
    ],
];

/**
 * Provide miscellaneous methods that are used by the data configuration array.
 */
class tl_apartments
{
    public function getZielgruppen(): array
    {
        $return = [];
        $result = \Contao\Database::getInstance()->execute('SELECT * FROM tl_zielgruppen ORDER BY zielgruppe');

        while ($result->next()) {
            $return[$result->id] = $result->zielgruppe;
        }

        return $return;
    }

    public function getZuweisendestellen(): array
    {
        $return = [];
        $result = \Contao\Database::getInstance()->execute('SELECT * FROM tl_zuweisendestellen ORDER BY zuweisendestelle');

        while ($result->next()) {
            $return[$result->id] = $result->zuweisendestelle;
        }

        return $return;
    }

    public function getAngebotstypen(): array
    {
        $return = [];
        $result = \Contao\Database::getInstance()->execute('SELECT * FROM tl_angebotstypen ORDER BY angebotstyp');

        while ($result->next()) {
            $return[$result->id] = $result->angebotstyp;
        }

        return $return;
    }
}