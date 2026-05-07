<?php

declare(strict_types=1);

namespace Guycollegmbh\ApartmentsBundle\Module;

use Contao\Database;
use Contao\FilesModel;
use Contao\Module;
use Contao\Input;

class ApartmentsDetailModule extends Module
{
    protected $strTemplate = 'mod_apartments_detail';

    protected function compile(): void
    {
        // Hole die Objektnummer aus der URL
        $objektnummerUrl = Input::get('id');

        if (!$objektnummerUrl) {
            $this->Template->apartment = null;
            $this->Template->error = 'Keine Wohnung ausgewählt.';
            return;
        }

        // Normalisiere URL-Parameter (decode)
        $objektnummerUrl = urldecode($objektnummerUrl);
        $objektnummerUrl = trim($objektnummerUrl);

        // Hole ALLE veröffentlichten Wohnungen
        $apartments = Database::getInstance()
            ->prepare('SELECT * FROM tl_apartments WHERE published = ?')
            ->execute(1);

        $apartmentData = null;

        while ($apartments->next()) {
            $row = $apartments->row();

            // HTML-Entities dekodieren
            $objektnr = html_entity_decode($row['objektnummer'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            // Entferne alles in Klammern
            $objektnr = preg_replace('/\s*\([^)]*\)/', '', $objektnr);
            $objektnr = trim($objektnr);

            // Direkter Vergleich mit Punkten
            if ($objektnr === $objektnummerUrl) {
                $apartmentData = $row;
                break;
            }
        }

        if (!$apartmentData) {
            $this->Template->apartment = null;
            $this->Template->error = 'Wohnung nicht gefunden.';
            return;
        }

        // Bilder vorbereiten
        if ($apartmentData['imagegrundriss']) {
            $file = FilesModel::findByUuid($apartmentData['imagegrundriss']);
            $apartmentData['imagegrundriss_path'] = $file ? $file->path : null;
        }

        if ($apartmentData['imageetagedetail']) {
            $file = FilesModel::findByUuid($apartmentData['imageetagedetail']);
            $apartmentData['imageetage_path'] = $file ? $file->path : 'files/apartments/visualEtageDetails/defaultetagedetails.png';
        } else {
            $apartmentData['imageetage_path'] = 'files/apartments/visualEtageDetails/defaultetagedetails.png';
        }

        if ($apartmentData['grundrisspdf']) {
            $file = FilesModel::findByUuid($apartmentData['grundrisspdf']);
            $apartmentData['grundrisspdf_path'] = $file ? $file->path : null;
            $apartmentData['grundrisspdf_name'] = $file ? $file->name : 'Grundriss';
        }

        // CHF-Beträge mit Tausendertrennzeichen formatieren (Apostroph)
        foreach (['nettomietzins', 'nebenkosten', 'bruttomietzins'] as $field) {
            if (!empty($apartmentData[$field])) {
                $numeric = (float) str_replace(["'", "'", ' '], '', $apartmentData[$field]);
                $apartmentData[$field] = number_format($numeric, 0, '.', "'");
            }
        }

        $this->Template->apartment = $apartmentData;
        $this->Template->error = null;
    }
}