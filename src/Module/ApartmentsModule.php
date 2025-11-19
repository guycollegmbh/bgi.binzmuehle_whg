<?php

declare(strict_types=1);

namespace Guycollegmbh\ApartmentsBundle\Module;

use Contao\BackendTemplate;
use Contao\Database;
use Contao\Module;
use Contao\System;

class ApartmentsListModule extends Module
{
    protected $strTemplate = 'mod_apartments_list';

    protected function compile(): void
    {
        // Hole alle veröffentlichten Apartments
        $apartments = Database::getInstance()
            ->prepare('SELECT * FROM tl_apartments WHERE published = ? ORDER BY bauetappe, zeile, objektnummer')
            ->execute(1);

        $apartmentsList = [];
        while ($apartments->next()) {
            $apartmentsList[] = $apartments->row();
        }

        $this->Template->apartments = $apartmentsList;
    }
}