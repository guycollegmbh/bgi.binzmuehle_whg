<?php

declare(strict_types=1);

namespace Guycollegmbh\ApartmentsBundle\Module;

use Contao\Database;
use Contao\Module;
use Contao\FilesModel;
use Contao\Input;

class ApartmentsListModule extends Module
{
    protected $strTemplate = 'mod_apartments_list';

    protected function compile(): void
    {
        $db = Database::getInstance();

        // Basis-Query
        $query = 'SELECT * FROM tl_apartments WHERE published = ?';
        $params = [1];

        // Filter verarbeiten
        $where = [];

        if (Input::get('status')) {
            $where[] = 'status = ?';
            $params[] = Input::get('status');
        }

        if (Input::get('zimmer')) {
            $where[] = 'zimmer = ?';
            $params[] = Input::get('zimmer');
        }

        if (Input::get('bauetappe')) {
            $where[] = 'bauetappe = ?';
            $params[] = Input::get('bauetappe');
        }

        if (!empty($where)) {
            $query .= ' AND ' . implode(' AND ', $where);
        }

        $query .= ' ORDER BY objektnummer ASC';

        // Query ausführen
        $result = $db->prepare($query)->execute(...$params);

        // Apartments mit Pfaden
        $apartments = [];
        while ($result->next()) {
            $data = $result->row();

            // URL-freundliche Objektnummer (mit Punkten, aber ohne Klammern)
            $objektnr = html_entity_decode($data['objektnummer'], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            $objektnr = preg_replace('/\s*\([^)]*\)/', '', $objektnr); // Entferne Klammern
            $data['objektnummer_url'] = urlencode(trim($objektnr)); // URL-encode für Sonderzeichen

            // PDF-Pfad generieren falls vorhanden
            if ($data['grundrisspdf']) {
                $fileModel = FilesModel::findByUuid($data['grundrisspdf']);
                if ($fileModel) {
                    $data['grundrisspdf_path'] = $fileModel->path;
                }
            }

            // Grundriss-Bild Pfad generieren
            if ($data['imagegrundriss']) {
                $fileModel = FilesModel::findByUuid($data['imagegrundriss']);
                if ($fileModel) {
                    $data['imagegrundriss_path'] = $fileModel->path;
                }
            }

            // Etagen-Bild Pfad generieren
            if ($data['imageetage']) {
                $fileModel = FilesModel::findByUuid($data['imageetage']);
                if ($fileModel) {
                    $data['imageetage_path'] = $fileModel->path;
                }
            }

            $apartments[] = $data;
        }

        // Filter-Optionen
        $statusOptions = $db->execute('SELECT DISTINCT status FROM tl_apartments WHERE published = 1 ORDER BY status')->fetchAllAssoc();
        $zimmerOptions = $db->execute('SELECT DISTINCT zimmer FROM tl_apartments WHERE published = 1 ORDER BY zimmer')->fetchAllAssoc();
        $bauetappeOptions = $db->execute('SELECT DISTINCT bauetappe FROM tl_apartments WHERE published = 1 ORDER BY bauetappe')->fetchAllAssoc();

        // Template-Variablen
        $this->Template->apartments = $apartments;
        $this->Template->statusOptions = $statusOptions;
        $this->Template->zimmerOptions = $zimmerOptions;
        $this->Template->bauetappeOptions = $bauetappeOptions;
        $this->Template->currentStatus = Input::get('status');
        $this->Template->currentZimmer = Input::get('zimmer');
        $this->Template->currentBauetappe = Input::get('bauetappe');
    }
}