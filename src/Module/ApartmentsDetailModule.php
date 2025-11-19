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
        $objektnummer = Input::get('id');

        // DEBUG
        \System::log('Gesuchte Objektnummer: ' . $objektnummer, __METHOD__, TL_GENERAL);

        if (!$objektnummer) {
            $this->Template->apartment = null;
            $this->Template->error = 'Keine Wohnung ausgewählt.';
            return;
        }

        // Hole die Wohnung anhand der Objektnummer aus der Datenbank
        $apartment = Database::getInstance()
            ->prepare('SELECT * FROM tl_apartments WHERE objektnummer = ? AND published = ?')
            ->limit(1)
            ->execute($objektnummer, 1);

        // DEBUG
        \System::log('Gefundene Zeilen: ' . $apartment->numRows, __METHOD__, TL_GENERAL);

        if ($apartment->numRows < 1) {
            $this->Template->apartment = null;
            $this->Template->error = 'Wohnung nicht gefunden. Gesuchte Objektnummer: ' . $objektnummer;
            return;
        }

        $apartmentData = $apartment->row();

        // Bilder vorbereiten
        if ($apartmentData['imagegrundriss']) {
            $file = FilesModel::findByUuid($apartmentData['imagegrundriss']);
            $apartmentData['imagegrundriss_path'] = $file ? $file->path : null;
        }

        if ($apartmentData['imageetage']) {
            $file = FilesModel::findByUuid($apartmentData['imageetage']);
            $apartmentData['imageetage_path'] = $file ? $file->path : null;
        }

        if ($apartmentData['grundrisspdf']) {
            $file = FilesModel::findByUuid($apartmentData['grundrisspdf']);
            $apartmentData['grundrisspdf_path'] = $file ? $file->path : null;
            $apartmentData['grundrisspdf_name'] = $file ? $file->name : 'Grundriss';
        }

        $this->Template->apartment = $apartmentData;
        $this->Template->error = null;
    }
}