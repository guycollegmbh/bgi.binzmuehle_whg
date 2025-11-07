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
            'mode'        => DataContainer::MODE_SORTABLE, 
            'fields'      => [, 'objektnummer', 'bauetappe', 'zeile'],  
            'flag'        => DataContainer::SORT_ASC,  // Aufsteigend (besser für Zahlen)
            'panelLayout' => 'filter;sort,search;limit', 
        ],
        'label' => [
            'fields' => ['objektnummer', 'bezeichnung', 'etage', 'zimmer', 'bauetappe', 'zeile'],
            'format' => '<strong>%s</strong> - %s <span style="color:#999;">(%s | %s Zi. | Bauetappe:%s | %s)</span>',
        ],
        'operations' => [
            'edit' => [
                'href'  => 'act=edit',
                'icon'  => 'edit.svg',
                'label' => &$GLOBALS['TL_LANG']['tl_apartments']['edit'],
            ],
            'copy' => [
                'href'  => 'act=copy',
                'icon'  => 'copy.svg',
                'label' => &$GLOBALS['TL_LANG']['tl_apartments']['copy'],
            ],
            'cut' => [
                'href'  => 'act=cut',
                'icon'  => 'cut.svg',
                'label' => &$GLOBALS['TL_LANG']['tl_apartments']['cut'],
            ],
            'delete' => [
                'href'       => 'act=delete',
                'icon'       => 'delete.svg',
                'label'      => &$GLOBALS['TL_LANG']['tl_apartments']['delete'],
                'attributes' => 'onclick="if(!confirm(\'' . ($GLOBALS['TL_LANG']['MSC']['deleteConfirm'] ?? '') . '\'))return false;Backend.getScrollOffset()"',
            ],
            'toggle' => [
                'icon'       => 'visible.svg',
                'attributes' => 'onclick="Backend.getScrollOffset();return AjaxRequest.toggleVisibility(this,%s)"',
            ],
            'show' => [
                'href'  => 'act=show',
                'icon'  => 'show.svg',
                'label' => &$GLOBALS['TL_LANG']['tl_apartments']['show'],
            ],
        ],
    ],
    'palettes' => [
        'default' => '{allgemein_legend},objektnummer,bezeichnung,bauetappe,zeile,adresse,etage,zimmer,flaeche;{kosten_legend},nettomietzins,nebenkosten,bruttomietzins;{files_legend},grundriss;{published_legend},published',
    ],
    'fields' => [
        'id' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'autoincrement' => true],
        ],
        'tstamp' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0],
        ],
        'objektnummer' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_apartments']['objektnummer'],
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50', 'maxlength' => 255, 'mandatory' => true],
            'sql'       => ['type' => 'string', 'length' => 255, 'default' => ''],
        ],
        'bezeichnung' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_apartments']['bezeichnung'],
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50', 'maxlength' => 255, 'mandatory' => true],
            'sql'       => ['type' => 'string', 'length' => 255, 'default' => ''],
        ],
        'bauetappe' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_apartments']['bauetappe'],
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'inputType' => 'select',
            'options'   => ['1', '2'],
            'eval'      => ['tl_class' => 'w50 m12', 'includeBlankOption' => true],
            'sql'       => ['type' => 'string', 'length' => 8, 'default' => ''],
        ],
        'zeile' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_apartments']['zeile'],
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'inputType' => 'select',
            'options'   => ['Zeile 1', 'Zeile 2', 'Zeile 3'],
            'eval'      => ['tl_class' => 'w50 m12', 'includeBlankOption' => true],
            'sql'       => ['type' => 'string', 'length' => 8, 'default' => ''],
        ],
        'adresse' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_apartments']['adresse'],
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'inputType' => 'select',
            'options'   => [
                'Binderweg 1', 'Binderweg 2', 'Binderweg 3', 'Binderweg 4', 'Binderweg 5',
                'Binderweg 6', 'Binderweg 7', 'Binderweg 8', 'Binderweg 9', 'Binderweg 10',
                'Zelghalde 30', 'Zelghalde 31', 'Zelghalde 32', 'Zelghalde 33', 'Zelghalde 34',
                'Zelghalde 35', 'Zelghalde 36', 'Zelghalde 37', 'Zelghalde 38', 'Zelghalde 39',
                'Kügeliloostrasse 67', 'Kügeliloostrasse 69', 'Kügeliloostrasse 65',
            ],
            'eval'      => ['tl_class' => 'w50 m12', 'includeBlankOption' => true],
            'sql'       => ['type' => 'string', 'length' => 32, 'default' => ''],
        ],
        'etage' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_apartments']['etage'],
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'inputType' => 'select',
            'options'   => ['EG', 'DG', '1. OG', '2. OG', '3. OG'],
            'eval'      => ['tl_class' => 'w50 m12', 'includeBlankOption' => true],
            'sql'       => ['type' => 'string', 'length' => 8, 'default' => ''],
        ],
        'zimmer' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_apartments']['zimmer'],
            'search'    => true,
            'sorting'   => true,
            'filter'    => true,
            'inputType' => 'select',
            'options'   => ['2', '2.5', '3.5', '4.5', '5.5'],
            'eval'      => ['tl_class' => 'w50 m12', 'includeBlankOption' => true],
            'sql'       => ['type' => 'string', 'length' => 4, 'default' => ''],
        ],
        'flaeche' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_apartments']['flaeche'],
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50 m12', 'maxlength' => 255],
            'sql'       => ['type' => 'string', 'length' => 255, 'default' => ''],
        ],
        'nettomietzins' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_apartments']['nettomietzins'],
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50 m12', 'maxlength' => 255],
            'sql'       => ['type' => 'string', 'length' => 255, 'default' => ''],
        ],
        'nebenkosten' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_apartments']['nebenkosten'],
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50 m12', 'maxlength' => 255],
            'sql'       => ['type' => 'string', 'length' => 255, 'default' => ''],
        ],
        'bruttomietzins' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_apartments']['bruttomietzins'],
            'search'    => true,
            'inputType' => 'text',
            'eval'      => ['tl_class' => 'w50 m12', 'maxlength' => 255],
            'sql'       => ['type' => 'string', 'length' => 255, 'default' => ''],
        ],
        'grundriss' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_apartments']['grundriss'],
            'inputType' => 'fileTree',
            'eval'      => [
                'fieldType'  => 'radio',
                'filesOnly'  => true,
                'extensions' => Config::get('validImageTypes'),
                'tl_class'   => 'w100 clr',
            ],
            'sql' => [
                'type'    => 'binary',
                'length'  => 16,
                'notnull' => false,
                'fixed'   => true,
            ],
        ],
        'published' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_apartments']['published'],
            'inputType' => 'checkbox',
            'toggle'    => true,
            'eval'      => ['tl_class' => 'clr w50 m12'],
            'sql'       => ['type' => 'boolean', 'default' => false],
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