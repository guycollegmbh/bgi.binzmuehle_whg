<?php

declare(strict_types=1);

namespace Guycollegmbh\ApartmentsBundle\Module;

use Contao\Database;
use Contao\Module;
use Contao\Environment;
use Contao\Input;

class ApartmentsDetailModule extends Module
{
    protected $strTemplate = 'mod_apartments_detail';

    protected function compile(): void
    {
        // Hole die ID aus der URL (z.B. /wohnungen/detail/5)
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

        $this->Template->apartment = $apartment->row();
        $this->Template->error = null;
    }
}