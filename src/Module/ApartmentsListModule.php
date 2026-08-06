<?php

declare(strict_types=1);

namespace Guycollegmbh\ApartmentsBundle\Module;

use Contao\Database;
use Contao\Module;
use Contao\FilesModel;
use Contao\Input;
use Contao\PageModel;

class ApartmentsListModule extends Module
{
    protected $strTemplate = 'mod_apartments_list';

    protected function compile(): void
    {
        $db = Database::getInstance();

        // Basis-Query - Wohnungen und Jokerzimmer anzeigen, Bauetappe 2 ausblenden
        $query = 'SELECT * FROM tl_apartments WHERE published = ? AND (bezeichnung = ? OR bezeichnung = ?) AND bauetappe != ?';
        $params = [1, 'Wohnung', 'Jokerzimmer', '2'];

        // Filter verarbeiten
        $where = [];

        $statusFilter = Input::get('status') !== null ? Input::get('status') : '';
        if ($statusFilter !== '') {
            $where[] = 'status = ?';
            $params[] = $statusFilter;
        }

        if (Input::get('zimmer')) {
            $where[] = 'zimmer = ?';
            $params[] = Input::get('zimmer');
        }

        if (Input::get('bauetappe')) {
            $where[] = 'bauetappe = ?';
            $params[] = Input::get('bauetappe');
        }

        if (Input::get('zeile')) {
            $where[] = 'zeile = ?';
            $params[] = Input::get('zeile');
        }

        if (Input::get('flaeche')) {
            $where[] = 'flaeche = ?';
            $params[] = Input::get('flaeche');
        }

        if (Input::get('etage')) {
            $where[] = 'etage = ?';
            $params[] = Input::get('etage');
        }

        // Preisfilter (Bruttomietzins)
        if (Input::get('minPrice')) {
            $where[] = 'CAST(REPLACE(REPLACE(bruttomietzins, \'\'\'\', \'\'), \',\', \'.\') AS DECIMAL(10,2)) >= ?';
            $params[] = Input::get('minPrice');
        }

        if (Input::get('maxPrice')) {
            $where[] = 'CAST(REPLACE(REPLACE(bruttomietzins, \'\'\'\', \'\'), \',\', \'.\') AS DECIMAL(10,2)) <= ?';
            $params[] = Input::get('maxPrice');
        }

        // Flächenfilter
        if (Input::get('minArea')) {
            $where[] = 'CAST(REPLACE(flaeche, \',\', \'.\') AS DECIMAL(10,2)) >= ?';
            $params[] = Input::get('minArea');
        }

        if (Input::get('maxArea')) {
            $where[] = 'CAST(REPLACE(flaeche, \',\', \'.\') AS DECIMAL(10,2)) <= ?';
            $params[] = Input::get('maxArea');
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

            // CHF-Betrag mit Tausendertrennzeichen formatieren
            if (!empty($data['bruttomietzins'])) {
                $numeric = (float) str_replace(["'", "'", ' '], '', $data['bruttomietzins']);
                $data['bruttomietzins'] = number_format($numeric, 0, '.', "'");
            }

            $apartments[] = $data;
        }

        // Filter-Optionen - für Wohnungen und Jokerzimmer, Bauetappe 2 ausblenden
        $statusOptions = $db->execute('SELECT DISTINCT status FROM tl_apartments WHERE published = 1 AND (bezeichnung = \'Wohnung\' OR bezeichnung = \'Jokerzimmer\') AND bauetappe != \'2\' ORDER BY status')->fetchAllAssoc();
        $zimmerOptions = $db->execute('SELECT DISTINCT zimmer FROM tl_apartments WHERE published = 1 AND (bezeichnung = \'Wohnung\' OR bezeichnung = \'Jokerzimmer\') AND bauetappe != \'2\' ORDER BY zimmer')->fetchAllAssoc();
        $bauetappeOptions = $db->execute('SELECT DISTINCT bauetappe FROM tl_apartments WHERE published = 1 AND (bezeichnung = \'Wohnung\' OR bezeichnung = \'Jokerzimmer\') AND bauetappe != \'2\' ORDER BY bauetappe')->fetchAllAssoc();
        $zeileOptions = $db->execute('SELECT DISTINCT zeile FROM tl_apartments WHERE published = 1 AND (bezeichnung = \'Wohnung\' OR bezeichnung = \'Jokerzimmer\') AND bauetappe != \'2\' AND zeile != \'\' ORDER BY zeile')->fetchAllAssoc();
        $etageOptions = $db->execute('SELECT DISTINCT etage FROM tl_apartments WHERE published = 1 AND (bezeichnung = \'Wohnung\' OR bezeichnung = \'Jokerzimmer\') AND bauetappe != \'2\' AND etage != \'\' ORDER BY FIELD(etage, \'UG\', \'EG\', \'EG/1.OG\', \'1.OG\', \'2.OG\', \'3.OG\', \'DG\')')->fetchAllAssoc();
        $flaechemOptions = $db->execute('SELECT DISTINCT flaeche FROM tl_apartments WHERE published = 1 AND (bezeichnung = \'Wohnung\' OR bezeichnung = \'Jokerzimmer\') AND bauetappe != \'2\' AND flaeche != \'\' ORDER BY CAST(flaeche AS DECIMAL(10,2))')->fetchAllAssoc();
        $flaeche = $db->execute('SELECT MIN(CAST(flaeche AS DECIMAL(10,2))) as min_flaeche, MAX(CAST(flaeche AS DECIMAL(10,2))) as max_flaeche FROM tl_apartments WHERE published = 1 AND (bezeichnung = \'Wohnung\' OR bezeichnung = \'Jokerzimmer\') AND bauetappe != \'2\' AND flaeche != \'\'')->fetchAssoc();
        $minFlaeche = (int) floor((float) ($flaeche['min_flaeche'] ?? 0));
        $maxFlaeche = (int) ceil((float) ($flaeche['max_flaeche'] ?? 300));
        $preis = $db->execute('SELECT MIN(CAST(REPLACE(bruttomietzins, \'\'\'\', \'\') AS DECIMAL(10,2))) as min_preis, MAX(CAST(REPLACE(bruttomietzins, \'\'\'\', \'\') AS DECIMAL(10,2))) as max_preis FROM tl_apartments WHERE published = 1 AND (bezeichnung = \'Wohnung\' OR bezeichnung = \'Jokerzimmer\') AND bauetappe != \'2\' AND bruttomietzins != \'\'')->fetchAssoc();
        $minPreis = (int) floor((float) ($preis['min_preis'] ?? 0));
        $rawMaxPreis = (int) ceil((float) ($preis['max_preis'] ?? 5000));
        // Round up to next valid step boundary (step=50 in slider) so the browser doesn't snap below the highest price
        $maxPreis = $minPreis + (int) (ceil(($rawMaxPreis - $minPreis) / 50.0) * 50);

        // Template-Variablen
        $this->Template->apartments = $apartments;
        $this->Template->statusOptions = $statusOptions;
        $this->Template->zimmerOptions = $zimmerOptions;
        $this->Template->bauetappeOptions = $bauetappeOptions;
        $this->Template->zeileOptions = $zeileOptions;
        $this->Template->currentStatus = Input::get('status') !== null ? Input::get('status') : '';
        $this->Template->currentZimmer = Input::get('zimmer');
        $this->Template->currentBauetappe = Input::get('bauetappe');
        $this->Template->currentZeile = Input::get('zeile');
        $this->Template->etageOptions = $etageOptions;
        $this->Template->currentEtage = Input::get('etage');
        $this->Template->flaechemOptions = $flaechemOptions;
        $this->Template->currentFlaeche = Input::get('flaeche');
        $this->Template->minFlaeche = $minFlaeche;
        $this->Template->maxFlaeche = $maxFlaeche;
        $this->Template->currentMinFlaeche = Input::get('minArea') ?: $minFlaeche;
        $this->Template->currentMaxFlaeche = Input::get('maxArea') ?: $maxFlaeche;
        $this->Template->minPreis = $minPreis;
        $this->Template->maxPreis = $maxPreis;
        $this->Template->currentMinPreis = Input::get('minPrice') ?: $minPreis;
        $this->Template->currentMaxPreis = Input::get('maxPrice') ?: $maxPreis;

        // Detail-URL aus jumpTo generieren, Fallback auf bisherigen Pfad
        $detailUrl = 'wohnungen/details';
        if ($this->jumpTo) {
            $page = PageModel::findById($this->jumpTo);
            if ($page) {
                $detailUrl = $page->getFrontendUrl();
            }
        }
        $this->Template->detail_url = $detailUrl;
    }
}