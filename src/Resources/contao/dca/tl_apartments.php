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
            'fields' => ['marketingtitel'],
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout' => 'search,limit',
        ],
        'label' => [
            'fields' => ['marketingtitel'],
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
        'default' => '{beschreibung_legend},titel,marketingtitel,beschreibung,seitenbild;{zusaetlicheinformationen_legend},angebotstyp,zuweisendestelle,standort,zielgruppe;{detailseite_legend},detailseite,target;{links_legend},linkueberschrift,links;{published_legend},published',
    ],
    'fields' => [
        'id' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'autoincrement' => true],
        ],
        'tstamp' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
        ],
        'titel' => [
            'label' => &$GLOBALS['TL_LANG']['tl_apartments']['titel'],
            'search' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50', 'maxlength' => 255, 'mandatory' => true],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
        ],
        'marketingtitel' => [
            'label' => &$GLOBALS['TL_LANG']['tl_apartments']['marketingtitel'],
            'search' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50', 'maxlength' => 255, 'mandatory' => true],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
        ],
        'beschreibung' => [
            'label' => &$GLOBALS['TL_LANG']['tl_apartments']['beschreibung'],
            'search' => true,
            'inputType' => 'textarea',
            'eval' => [
                'rte' => 'tinyMCE',
                'tl_class' => 'w100 clr',
            ],
            'sql' => [
                'type' => 'text',
                'notnull' => false,
            ],
        ],
        'seitenbild' => [
            'label' => &$GLOBALS['TL_LANG']['tl_apartments']['seitenbild'],
            'inputType' => 'fileTree',
            'eval' => [
                'fieldType' => 'radio',
                'filesOnly' => true,
                'extensions' => Config::get('validImageTypes'),
                'mandatory' => true,
                'tl_class' => 'w50 clr',
            ],
            'sql' => [
                'type' => 'binary',
                'length' => 16,
                'notnull' => false,
                'fixed' => true,
            ],
        ],
        'angebotstyp' => [
            'label' => &$GLOBALS['TL_LANG']['tl_apartments']['angebotstyp'],
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
        'zuweisendestelle' => [
            'label' => &$GLOBALS['TL_LANG']['tl_apartments']['zuweisendestelle'],
            'search' => true,
            'inputType' => 'checkbox',
            'eval' => [
                'mandatory' => false,
                'fieldType' => 'checkbox',
                'includeBlankOption' => true,
                'tl_class' => 'w50 clr m12',
                'multiple' => true,
            ],
            'options_callback' => ['tl_apartments', 'getZuweisendestellen'],
            'sql' => 'blob NULL',
        ],
        'standort' => [
            'label' => &$GLOBALS['TL_LANG']['tl_apartments']['standort'],
            'search' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50 clr', 'maxlength' => 255],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
        ],
        'zielgruppe' => [
            'label' => &$GLOBALS['TL_LANG']['tl_apartments']['zielgruppe'],
            'search' => true,
            'inputType' => 'select',
            'options_callback' => ['tl_apartments', 'getZielgruppen'],
            'eval' => [
                'tl_class' => 'w50 clr',
                'maxlength' => 255,
                'includeBlankOption' => true,
            ],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
        ],
        'detailseite' => [
            'label' => &$GLOBALS['TL_LANG']['tl_apartments']['detailseite'],
            'search' => true,
            'inputType' => 'pageTree',
            'foreignKey' => 'tl_page.title',
            'eval' => [
                'fieldType' => 'radio',
                'tl_class' => 'clr',
            ],
            'sql' => 'int(10) unsigned NOT NULL default 0',
            'relation' => ['type' => 'hasOne', 'load' => 'lazy'],
        ],
        'target' => [
            'label' => &$GLOBALS['TL_LANG']['tl_apartments']['target'],
            'inputType' => 'checkbox',
            'eval' => ['tl_class' => 'clr m12'],
            'sql' => [
                'type' => 'boolean',
                'default' => false,
            ],
        ],
        'linkueberschrift' => [
            'label' => &$GLOBALS['TL_LANG']['tl_apartments']['linkueberschrift'],
            'search' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50', 'maxlength' => 255],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => ''],
        ],
        'links' => [
            'label' => &$GLOBALS['TL_LANG']['tl_apartments']['links'],
            'exclude' => true,
            'inputType' => 'multiColumnWizard',
            'eval' => [
                'columnFields' => [
                    'linktext' => [
                        'label' => &$GLOBALS['TL_LANG']['tl_apartments']['linktext'],
                        'exclude' => true,
                        'inputType' => 'text',
                        'eval' => [
                            'style' => 'width:400px',
                        ],
                    ],
                    'linktitel' => [
                        'label' => &$GLOBALS['TL_LANG']['tl_apartments']['linktitel'],
                        'exclude' => true,
                        'inputType' => 'text',
                        'eval' => [
                            'style' => 'width:400px',
                        ],
                    ],
                    'linkurl' => [
                        'label' => &$GLOBALS['TL_LANG']['tl_apartments']['linkurl'],
                        'exclude' => true,
                        'eval' => [
                            'dcaPicker' => true,
                            'style' => 'width:400px',
                        ],
                        'inputType' => 'text',
                    ],
                    'linkblank' => [
                        'label' => &$GLOBALS['TL_LANG']['tl_apartments']['linkblank'],
                        'eval' => ['style' => 'width:100px'],
                        'inputType' => 'checkbox',
                    ],
                ],
                'tl_class' => 'clr',
            ],
            'sql' => 'blob NULL',
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