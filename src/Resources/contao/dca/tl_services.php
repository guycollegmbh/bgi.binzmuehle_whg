<?php
/*
 * Copyright GUYCOLLE GMBH
 */

 use Contao\Backend;
 use Contao\BackendUser;
 use Contao\Config;
 use Contao\CoreBundle\Security\ContaoCorePermissions;
 use Contao\Database;
 use Contao\DataContainer;
 use Contao\Date;
 use Contao\DC_Table;
 use Contao\Input;
 use Contao\LayoutModel;
 use Contao\PageModel;
 use Contao\StringUtil;
 use Contao\System;

/**
 * Services DCA
 *
 * @author GUYCOLLE GMBH / Patrick Grob
 */

$GLOBALS['TL_DCA']['tl_services'] = [
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
            'panelLayout' => 'search,limit'
        ],
        'label' => [
            // Änderung gemäss E-Mail Rebecca 06012025
            'fields' => ['marketingtitel'],
            'format' => '%s',
        ],
        'operations' => [
            'edit',
            'copy',
            'delete',
            'show',
            'toggle'
        ],
    ],
	'fields' => [
        'id' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'autoincrement' => true],
        ],
        'tstamp' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0]
        ],
        'titel' => [
            'label' => &$GLOBALS['TL_LANG']['tl_services']['titel'],
            'search' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50', 'maxlength' => 255, 'mandatory' => true],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => '']
        ],
        'marketingtitel' => [
            'label' => &$GLOBALS['TL_LANG']['tl_services']['marketingtitel'],
            'search' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50', 'maxlength' => 255, 'mandatory' => true],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => '']
        ],
        'beschreibung' => [
            'label' => &$GLOBALS['TL_LANG']['tl_services']['beschreibung'],
            'search' => true,
            'inputType' => 'textarea',
            'eval' => [
                'rte' => 'tinyMCE',
                'tl_class' => 'w100 clr'
            ],
            'sql' => [
                'type' => 'text',
                'notnull' => false,
            ],
        ],
        'seitenbild' => [
            'label' => &$GLOBALS['TL_LANG']['tl_services']['seitenbild'],
            'inputType' => 'fileTree',
            'eval' => [
                'tl_class' => 'clr',
                'fieldType' => 'radio',
                'filesOnly' => true,
                'extensions' => \Contao\Config::get('validImageTypes'),
                'mandatory' => true,
                'tl_class' => 'w50',
            ],
            'sql' => [
                'type' => 'binary', 
                'length' => 16, 
                'notnull' => false, 
                'fixed' => true
            ],
        ],
        'angebotstyp' => [
            'label' => &$GLOBALS['TL_LANG']['tl_services']['angebotstyp'],
            'search' => true,
            'inputType' => 'checkbox',
            'eval' => ['mandatory'=>false,'fieldType'=>'checkbox','includeBlankOption'=>true,'tl_class'=>'w50 clr m12','multiple' => true],
            'options_callback'=> array('tl_services_functions','getAngebotstypen'),
            'sql' => "blob NULL"
        ],
        'zuweisendestelle' => [
            'label' => &$GLOBALS['TL_LANG']['tl_services']['zuweisendestelle'],
            'search' => true,
            'inputType' => 'checkbox',
            'eval' => ['mandatory'=>false,'fieldType'=>'checkbox','includeBlankOption'=>true,'tl_class'=>'w50 clr m12','multiple' => true],
            'options_callback'=> array('tl_services_functions','getZuweisendestellen'),
            'sql' => "blob NULL"
        ],
        'standort' => [
            'label' => &$GLOBALS['TL_LANG']['tl_services']['standort'],
            'search' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50 clr', 'maxlength' => 255],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => '']
        ],
        'zielgruppe' => [
            'label' => &$GLOBALS['TL_LANG']['tl_services']['zielgruppe'],
            'search' => true,
            'inputType' => 'select',
            'options_callback'=> array('tl_services_functions','getZielgruppen'),
            'eval' => ['tl_class' => 'w50 clr', 'maxlength' => 255,'includeBlankOption' => true,],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => '']
        ],
        'detailseite' => [
            'label' => &$GLOBALS['TL_LANG']['tl_services']['detailseite'],
            'search' => true,
            'inputType'     => 'pageTree',
            'rootNodes'     => array(9),
			'eval'          => array('fieldType'=>'radio', 'tl_class'=>'clr'),
			'sql'           => "blob NULL"
        ],
        'target' => [
            'label' => &$GLOBALS['TL_LANG']['tl_services']['target'],
            'inputType' => 'checkbox',
            'toggle' => true,
            'eval' => array('tl_class' => 'clr m12'),
            'sql' => [
            'type' => 'boolean',
            'default' => false,
            ],
        ],
        'linkueberschrift' => [
            'label' => &$GLOBALS['TL_LANG']['tl_services']['linkueberschrift'],
            'search' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50', 'maxlength' => 255],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => '']
        ],
        'links' => [
            'label'     => &$GLOBALS['TL_LANG']['tl_services']['links'],
            'exclude'   => true,
            'inputType' => 'multiColumnWizard',
            'eval'      => [
                'columnFields' => [
                    'linktext'      => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_services']['linktext'],
                        'exclude'   => true,
                        'inputType' => 'text',
                        'eval'      => [
                            'style' => 'width:400px',
                        ],
                    ],
                    'linktitel'      => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_services']['linktitel'],
                        'exclude'   => true,
                        'inputType' => 'text',
                        'eval'      => [
                            'style'  => 'width:400px',
                        ],
                    ],
                    'linkurl' => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_services']['linkurl'],
                        'exclude'   => true,
                        'eval' => array('dcaPicker' => true, 'style'  => 'width:400px'),
                        'inputType' => 'text',
                    ],
                    'linkblank' => [
                        'label'     => &$GLOBALS['TL_LANG']['tl_services']['linkblank'],
                        'label' => array('In neuem Fenster öffnen', ''),
                        'eval' => array('style'  => 'width:100px'),
                        'inputType' => 'checkbox',
                    ],
                ],
                'tl_class' => 'clr',
            ],
            'sql'       => 'blob NULL',
        ],
        'published' => [
            'label' => &$GLOBALS['TL_LANG']['tl_services']['published'],
            'inputType' => 'checkbox',
            'toggle' => true,
            'eval' => array('tl_class' => 'clr'),
            'sql' => [
            'type' => 'boolean',
            'default' => true,
            ],
        ],
    ],
	'palettes' => [
        'default' => '{beschreibung_legend},titel,marketingtitel,beschreibung,seitenbild;{zusaetlicheinformationen_legend},angebotstyp,zuweisendestelle,standort,zielgruppe;{detailseite_legend},detailseite,target;{links_legend},linkueberschrift,links;{published_legend},published'
    ],
];



class tl_services_functions extends Backend
{

    public function getZielgruppen($dc)
	{
		$return = array();
		$objZielgruppen = Database::getInstance()->execute('SELECT DISTINCT * FROM tl_zielgruppen');

		if ($objZielgruppen->numRows < 1)
		{
			return array();
		}
		while ($objZielgruppen->next())
		{
			$return[$objZielgruppen->id] = $objZielgruppen->zielgruppe;
		}
		return $return;
	}

    public function getZuweisendestellen($dc)
	{
		$return = array();
		$objZuweisendestellen = Database::getInstance()->execute('SELECT DISTINCT * FROM tl_zuweisendestellen');

		if ($objZuweisendestellen->numRows < 1)
		{
			return array();
		}
		while ($objZuweisendestellen->next())
		{
			$return[$objZuweisendestellen->id] = $objZuweisendestellen->zuweisendestelle;
		}
		return $return;
	}

    public function getAngebotstypen($dc)
	{
		$return = array();
		$objAngebotstypen = Database::getInstance()->execute('SELECT DISTINCT * FROM tl_angebotstypen');

		if ($objAngebotstypen->numRows < 1)
		{
			return array();
		}
		while ($objAngebotstypen->next())
		{
			$return[$objAngebotstypen->id] = $objAngebotstypen->angebotstyp;
		}
		return $return;
	}

}