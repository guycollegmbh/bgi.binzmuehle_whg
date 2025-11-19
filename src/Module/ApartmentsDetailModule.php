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
        // Hole die ID aus der URL
        $apartmentId = Input::get('id');

        if (!$apartmentId) {
            $this->Template->apartment = null;
            $this->Template->error = 'Keine Wohnung ausgewählt.';
            return;
        }

        // Hole die Wohnung aus der Datenbank
        $apartment = Database::getInstance()
            ->prepare('SELECT * FROM tl_apartments WHERE id = ? AND published = ?')
            ->limit(1)
            ->execute($apartmentId, 1);

        if ($apartment->numRows < 1) {
            $this->Template->apartment = null;
            $this->Template->error = 'Wohnung nicht gefunden.';
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