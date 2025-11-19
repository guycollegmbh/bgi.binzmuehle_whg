<?php

declare(strict_types=1);

namespace Guycollegmbh\ApartmentsBundle\Module;

use Contao\Database;
use Contao\Module;
use Contao\Controller;

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
            
            // HTML-Entities dekodieren
            $objektnr = html_entity_decode($data['objektnummer'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            // Entferne alles in Klammern
            $objektnr = preg_replace('/\s*\([^)]*\)/', '', $objektnr);
            // Entferne Punkte und Leerzeichen, behalte nur Zahlen
            $objektnr = preg_replace('/[^0-9]/', '', $objektnr);
            
            $data['objektnummer_url'] = $objektnr;
            $apartmentsList[] = $data;
        }

        $this->Template->apartments = $apartmentsList;
    }
}