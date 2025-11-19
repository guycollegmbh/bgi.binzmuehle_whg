<?php

declare(strict_types=1);

namespace Guycollegmbh\ApartmentsBundle\Module;

use Contao\Database;
use Contao\Module;
use Contao\StringUtil;

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
            $data = $apartments->row();
            // URL-sichere Objektnummer erstellen
            $data['objektnummer_url'] = preg_replace('/[^a-zA-Z0-9-]/', '', $data['objektnummer']);
            $apartmentsList[] = $data;
        }

        $this->Template->apartments = $apartmentsList;
    }
}