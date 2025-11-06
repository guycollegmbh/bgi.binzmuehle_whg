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
 * Hilfstabelle Zielgruppen DCA
 *
 * @author GUYCOLLE GMBH / Patrick Grob
 */

$GLOBALS['TL_DCA']['tl_zielgruppen'] = [
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
            'fields' => ['zielgruppe'],
            'flag' => DataContainer::SORT_INITIAL_LETTER_ASC,
            'panelLayout' => 'search,limit'
        ],
        'label' => [
            'fields' => ['zielgruppe'],
            'format' => '%s',
        ],
        'operations' => [
            'edit',
            'copy',
            'delete',
            'show',
        ],
    ],
	'fields' => [
        'id' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'autoincrement' => true],
        ],
        'tstamp' => [
            'sql' => ['type' => 'integer', 'unsigned' => true, 'default' => 0]
        ],
        'zielgruppe' => [
            'label' => &$GLOBALS['TL_LANG']['tl_zielgruppen']['zielgruppe'],
            'search' => true,
            'inputType' => 'text',
            'eval' => ['tl_class' => 'w50', 'maxlength' => 255, 'mandatory' => true],
            'sql' => ['type' => 'string', 'length' => 255, 'default' => '']
        ],
        'alias' => [
            'label' => &$GLOBALS['TL_LANG']['tl_zielgruppen']['alias'],
            'search' => true,
            'inputType' => 'text',
            'eval' => ['rgxp'=>'alias', 'doNotCopy'=>true, 'unique'=>true, 'maxlength'=>255, 'tl_class'=>'w50 clr'],
            'save_callback' => array
			(
				array('tl_zielgruppen', 'generateAlias')
			),
            'sql' => ['type' => 'string', 'length' => 255, 'default' => '']
        ],
    ],
	'palettes' => [
        'default' => '{zielgruppen_legend},zielgruppe,alias'
    ],
];


//Automatisches Erzuegen Alias "alias" aus Feld "zielgruppe"
class tl_zielgruppen extends Backend
{

	/**
	 * Auto-generate the event alias if it has not been set yet
	 *
	 * @param mixed         $varValue
	 * @param DataContainer $dc
	 *
	 * @return mixed
	 *
	 * @throws Exception
	 */
	public function generateAlias($varValue, DataContainer $dc)
	{
		$autoAlias = false;

		// Generate alias if there is none
		if ($varValue == '')
		{
			$autoAlias = true;
			$varValue = StringUtil::generateAlias($dc->activeRecord->zielgruppe);
		}

		$objAlias = $this->Database->prepare("SELECT id FROM tl_zielgruppen WHERE alias=? AND id!=?")->execute($varValue, $dc->id);

		// Check whether the event alias exists
		if ($objAlias->numRows)
		{
			if (!$autoAlias)
			{
				throw new Exception(sprintf($GLOBALS['TL_LANG']['ERR']['aliasExists'], $varValue));
			}

			$varValue .= '-' . $dc->id;
		}

		return $varValue;
	}
}	