<?php

declare(strict_types=1);

namespace Guycollegmbh\ApartmentsBundle\Controller\Backend;

use Contao\Backend;
use Contao\BackendTemplate;
use Contao\Database;
use Contao\Environment;
use Contao\Input;
use Contao\Message;
use Contao\System;
use Contao\FilesModel;
use Contao\Dbafs;
use Contao\Controller;
use PhpOffice\PhpSpreadsheet\IOFactory;
use Psr\Log\LoggerInterface;

class ApartmentsImportController extends Backend
{
    /**
     * Dateien-Synchronisierung (PDFs + Bilder) mit Timestamp-Check
     */
    public function syncPdfs()
    {
        $db = Database::getInstance();
        
        // Hole alle Apartments
        $apartments = $db->execute('SELECT id, objektnummer, grundrisspdf, imagegrundriss, imageetage, imageetagedetail FROM tl_apartments');
        
        $pdfLinked = 0;
        $pdfUpdated = 0;
        $pdfUnchanged = 0;
        $pdfNotFound = 0;
        
        $imgGrundLinked = 0;
        $imgGrundUpdated = 0;
        $imgGrundUnchanged = 0;
        $imgGrundNotFound = 0;
        
        $imgEtageLinked = 0;
        $imgEtageUpdated = 0;
        $imgEtageUnchanged = 0;
        $imgEtageNotFound = 0;

        $imgEtageDetailLinked = 0;
        $imgEtageDetailUpdated = 0;
        $imgEtageDetailUnchanged = 0;
        $imgEtageDetailNotFound = 0;
        
        $total = $apartments->numRows;
        
        while ($apartments->next()) {
            $objektnummer = $apartments->objektnummer;
            $apartmentId = $apartments->id;
            
            // 1. PDF Grundriss synchronisieren
            $result = $this->syncFileField(
                $objektnummer,
                $apartments->grundrisspdf,
                'grundrisspdf',
                $apartmentId,
                'findPdfByObjektnummer'
            );
            
            switch ($result) {
                case 'linked': $pdfLinked++; break;
                case 'updated': $pdfUpdated++; break;
                case 'unchanged': $pdfUnchanged++; break;
                case 'notfound': $pdfNotFound++; break;
            }
            
            // 2. Bild Grundriss synchronisieren
            $result = $this->syncFileField(
                $objektnummer,
                $apartments->imagegrundriss,
                'imagegrundriss',
                $apartmentId,
                'findImageGrundrissByObjektnummer'
            );
            
            switch ($result) {
                case 'linked': $imgGrundLinked++; break;
                case 'updated': $imgGrundUpdated++; break;
                case 'unchanged': $imgGrundUnchanged++; break;
                case 'notfound': $imgGrundNotFound++; break;
            }
            
            // 3. Bild Etage synchronisieren
            $result = $this->syncFileField(
                $objektnummer,
                $apartments->imageetage,
                'imageetage',
                $apartmentId,
                'findImageEtageByObjektnummer'
            );
            
            switch ($result) {
                case 'linked': $imgEtageLinked++; break;
                case 'updated': $imgEtageUpdated++; break;
                case 'unchanged': $imgEtageUnchanged++; break;
                case 'notfound': $imgEtageNotFound++; break;
            }

            // 4. Bild Etage Detail synchronisieren
            $result = $this->syncFileField(
                $objektnummer,
                $apartments->imageetagedetail,
                'imageetagedetail',
                $apartmentId,
                'findImageEtageDetailByObjektnummer'
            );

            switch ($result) {
                case 'linked': $imgEtageDetailLinked++; break;
                case 'updated': $imgEtageDetailUpdated++; break;
                case 'unchanged': $imgEtageDetailUnchanged++; break;
                case 'notfound': $imgEtageDetailNotFound++; break;
            }
        }
        
        // Detaillierte Message
        $messages = [];
        
        if ($pdfLinked + $pdfUpdated + $pdfUnchanged > 0 || $pdfNotFound > 0) {
            $messages[] = sprintf('PDFs: %d verknüpft, %d aktualisiert, %d unverändert, %d nicht gefunden',
                $pdfLinked, $pdfUpdated, $pdfUnchanged, $pdfNotFound);
        }
        
        if ($imgGrundLinked + $imgGrundUpdated + $imgGrundUnchanged > 0 || $imgGrundNotFound > 0) {
            $messages[] = sprintf('Grundriss-Bilder: %d verknüpft, %d aktualisiert, %d unverändert, %d nicht gefunden',
                $imgGrundLinked, $imgGrundUpdated, $imgGrundUnchanged, $imgGrundNotFound);
        }
        
        if ($imgEtageLinked + $imgEtageUpdated + $imgEtageUnchanged > 0 || $imgEtageNotFound > 0) {
            $messages[] = sprintf('Etagen-Bilder: %d verknüpft, %d aktualisiert, %d unverändert, %d nicht gefunden',
                $imgEtageLinked, $imgEtageUpdated, $imgEtageUnchanged, $imgEtageNotFound);
        }

        if ($imgEtageDetailLinked + $imgEtageDetailUpdated + $imgEtageDetailUnchanged > 0 || $imgEtageDetailNotFound > 0) {
            $messages[] = sprintf('Etagen-Detail-Bilder: %d verknüpft, %d aktualisiert, %d unverändert, %d nicht gefunden',
                $imgEtageDetailLinked, $imgEtageDetailUpdated, $imgEtageDetailUnchanged, $imgEtageDetailNotFound);
        }
        
        if (empty($messages)) {
            Message::addInfo('Keine Dateien zum Synchronisieren gefunden.');
        } else {
            Message::addConfirmation(sprintf(
                'Dateien synchronisiert (%d Wohnungen):<br>%s',
                $total,
                implode('<br>', $messages)
            ));
        }
        
        // Zurück zur Übersicht
        Controller::redirect(System::getReferer());
    }
    
    /**
     * Generische Sync-Funktion für ein Datei-Feld
     */
    protected function syncFileField($objektnummer, $currentUuid, $fieldName, $apartmentId, $finderMethod)
    {
        $db = Database::getInstance();
        
        // Suche neue Datei
        $newFileInfo = $this->$finderMethod($objektnummer);
        
        if (!$newFileInfo) {
            return 'notfound';
        }
        
        // Wenn noch keine Datei verknüpft ist
        if (!$currentUuid) {
            $db->prepare("UPDATE tl_apartments SET {$fieldName} = ? WHERE id = ?")
                ->execute($newFileInfo['uuid'], $apartmentId);
            return 'linked';
        }
        
        // Prüfe ob Update nötig ist (Timestamp-Vergleich)
        $currentFileModel = FilesModel::findByUuid($currentUuid);
        
        if (!$currentFileModel) {
            // Alte Datei existiert nicht mehr, verknüpfe neue
            $db->prepare("UPDATE tl_apartments SET {$fieldName} = ? WHERE id = ?")
                ->execute($newFileInfo['uuid'], $apartmentId);
            return 'updated';
        }
        
        // Vergleiche UUIDs - wenn unterschiedlich, immer aktualisieren
        if (bin2hex($currentUuid) === bin2hex($newFileInfo['uuid'])) {
            return 'unchanged';
        }

        $db->prepare("UPDATE tl_apartments SET {$fieldName} = ? WHERE id = ?")
            ->execute($newFileInfo['uuid'], $apartmentId);
        return 'updated';
    }
    
    /**
     * Excel-Import (OHNE Datei-Verknüpfung)
     */
    public function importApartments()
    {
        // Backend Template laden
        $template = new BackendTemplate('be_apartments_import');
        
        $template->action = Environment::get('request');
        $template->href = System::getReferer();
        $template->title = 'Zurück';
        
        // CSRF Token für Contao 5
        $csrfTokenManager = System::getContainer()->get('contao.csrf.token_manager');
        $template->requestToken = $csrfTokenManager->getToken(System::getContainer()->getParameter('contao.csrf_token_name'))->getValue();
        
        // Messages für Template
        $template->messages = Message::generate();
        $template->preview = null;
        $template->sheets = null;
        
        // Schritt 3: Import ausführen
        if (Input::post('FORM_SUBMIT') === 'tl_apartments_import_confirm') {
            $this->processImport();
            $this->redirect('contao?do=apartments&table=tl_apartments');
        }
        // Schritt 2: Vorschau generieren
        elseif (Input::post('FORM_SUBMIT') === 'tl_apartments_import_preview') {
            $preview = $this->generatePreview();
            if ($preview) {
                $template->preview = $preview;
            }
            $template->messages = Message::generate();
        }
        // Schritt 1: Tab-Auswahl anzeigen
        elseif (Input::post('FORM_SUBMIT') === 'tl_apartments_import') {
            $sheets = $this->getSheetsList();
            if ($sheets) {
                $template->sheets = $sheets;
            }
            $template->messages = Message::generate();
        }
        
        return $template->parse();
    }
    
    protected function getSheetsList()
    {
        $uploadedFile = $_FILES['import_file'] ?? null;
        
        if (!$uploadedFile || $uploadedFile['error'] !== UPLOAD_ERR_OK) {
            Message::addError('Bitte wählen Sie eine Excel-Datei aus.');
            return null;
        }
        
        try {
            $spreadsheet = IOFactory::load($uploadedFile['tmp_name']);
            $tempFile = $this->saveTempFile($uploadedFile['tmp_name']);
            
            $sheets = [];
            foreach ($spreadsheet->getAllSheets() as $worksheet) {
                $sheets[] = [
                    'name' => $worksheet->getTitle(),
                    'rowCount' => $worksheet->getHighestRow() - 4, // -4 für Titel, Datum, Leer, Header
                ];
            }
            
            return [
                'tempFile' => $tempFile,
                'list' => $sheets,
            ];
            
        } catch (\Exception $e) {
            Message::addError('Fehler beim Lesen der Datei: ' . $e->getMessage());
            return null;
        }
    }
    
    protected function generatePreview()
    {
        $tempFileName = Input::post('temp_file');
        $selectedSheets = Input::post('selected_sheets');
        
        if (!$tempFileName) {
            Message::addError('Keine Importdatei gefunden.');
            return null;
        }
        
        if (empty($selectedSheets) || !is_array($selectedSheets)) {
            Message::addError('Bitte wählen Sie mindestens einen Tab aus.');
            return null;
        }
        
        $tempFile = System::getContainer()->getParameter('kernel.project_dir') . '/system/tmp/' . $tempFileName;
        
        if (!file_exists($tempFile)) {
            Message::addError('Temporäre Datei nicht gefunden.');
            return null;
        }
        
        try {
            $spreadsheet = IOFactory::load($tempFile);
            
            $db = Database::getInstance();
            $preview = [
                'new' => [],
                'update' => [],
                'skip' => [],
                'tempFile' => $tempFileName,
                'selectedSheets' => $selectedSheets,
            ];
            
            // Nur ausgewählte Sheets durchgehen
            foreach ($spreadsheet->getAllSheets() as $worksheet) {
                $sheetName = $worksheet->getTitle();
                
                // Skip wenn nicht ausgewählt
                if (!in_array($sheetName, $selectedSheets)) {
                    continue;
                }
                
                // Zeilen durchgehen (Zeile 5 = erste Datenzeile)
                foreach ($worksheet->getRowIterator(5) as $row) {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);
                    
                    $data = [];
                    $colIndex = 0;
                    foreach ($cellIterator as $cell) {
                        // Bruttomietzins (Spalte 10) kann eine Formel sein - berechnet auslesen
                        $data[] = ($colIndex === 10) ? $cell->getCalculatedValue() : $cell->getValue();
                        $colIndex++;
                    }

                    $objektnummer = trim((string)($data[0] ?? ''));

                    if (empty($objektnummer)) {
                        // Prüfe ob die Zeile komplett leer ist
                        $hasData = false;
                        foreach (array_slice($data, 0, 11) as $cell) { // Nur erste 11 Spalten prüfen
                            if (!empty(trim((string)$cell))) {
                                $hasData = true;
                                break;
                            }
                        }
                        
                        if ($hasData) {
                            $preview['skip'][] = [
                                'reason' => 'Keine Objektnummer (Tab: ' . $sheetName . ')',
                                'data' => array_slice($data, 0, 11),
                            ];
                        }
                        continue;
                    }
                    
                    $apartmentData = [
                        'objektnummer' => trim((string)$objektnummer),
                        'bezeichnung' => trim((string)($data[1] ?? '')),
                        'bauetappe' => trim((string)($data[2] ?? '')),
                        'zeile' => trim((string)($data[3] ?? '')),
                        'adresse' => trim((string)($data[4] ?? '')),
                        'etage' => trim((string)($data[5] ?? '')),
                        'zimmer' => trim((string)($data[6] ?? '')),
                        'flaeche' => trim((string)($data[7] ?? '')),
                        'nettomietzins' => trim((string)($data[8] ?? '')),
                        'nebenkosten' => trim((string)($data[9] ?? '')),
                        'bruttomietzins' => trim((string)($data[10] ?? '')),
                        'status' => 'Frei', // Nur für neue Einträge
                    ];
                    
                    // Normalisierte Version für Vergleich
                    $objektnummerNormalized = $this->normalizeObjektnummer($objektnummer);
                    
                    // Prüfen ob Wohnung bereits existiert (mit normalisiertem Vergleich)
                    $existing = $db->execute('SELECT * FROM tl_apartments WHERE published = 1');
                    $existingMatch = null;
                    
                    while ($existing->next()) {
                        if ($this->normalizeObjektnummer($existing->objektnummer) === $objektnummerNormalized) {
                            $existingMatch = $existing->row();
                            break;
                        }
                    }
                    
                    if ($existingMatch !== null) {
                        // Update - Zeige was sich ändert (Status und Dateien ausschließen)
                        $changes = [];
                        
                        foreach ($apartmentData as $field => $newValue) {
                            // Status und Dateien nicht in Änderungen aufnehmen
                            if (in_array($field, ['status', 'grundrisspdf', 'imagegrundriss', 'imageetage'])) {
                                continue;
                            }
                            
                            $oldValue = trim((string)($existingMatch[$field] ?? ''));
                            $newValue = trim((string)$newValue);
                            
                            // Normalisiere Werte für Vergleich
                            $oldNormalized = $this->normalizeValue($oldValue);
                            $newNormalized = $this->normalizeValue($newValue);
                            
                            if ($oldNormalized !== $newNormalized) {
                                $changes[$field] = [
                                    'old' => $oldValue,
                                    'new' => $newValue,
                                ];
                            }
                        }
                        
                        // Nur als Update zählen wenn tatsächlich Änderungen vorhanden sind
                        if (!empty($changes)) {
                            $preview['update'][] = [
                                'id' => $existingMatch['id'],
                                'objektnummer' => $objektnummer,
                                'sheet' => $sheetName,
                                'changes' => $changes,
                                'data' => $apartmentData,
                            ];
                        }
                    } else {
                        // Neu
                        $apartmentData['sheet'] = $sheetName;
                        $preview['new'][] = $apartmentData;
                    }
                }
            }
            
            Message::addInfo(sprintf(
                'Vorschau (%d Tabs): %d neue Wohnungen, %d Updates, %d übersprungen',
                count($selectedSheets),
                count($preview['new']),
                count($preview['update']),
                count($preview['skip'])
            ));
            
            return $preview;
            
        } catch (\Exception $e) {
            Message::addError('Fehler beim Lesen der Datei: ' . $e->getMessage());
            
            $logger = System::getContainer()->get('monolog.logger.contao');
            if ($logger instanceof LoggerInterface) {
                $logger->error('Apartments Import Preview Error: ' . $e->getMessage(), ['exception' => $e]);
            }
            
            return null;
        }
    }
    
    /**
     * Findet PDF anhand Objektnummer und gibt UUID + Timestamp zurück
     */
    protected function findPdfByObjektnummer($objektnummer)
    {
        // Bereinige Objektnummer für Dateiname (entferne alles außer Zahlen und Punkte)
        $cleanNummer = preg_replace('/[^0-9.]/', '', $objektnummer);
        $filename = $cleanNummer . '.pdf';
        $path = 'files/apartments/pdfGrundriss/' . $filename;
        
        // Hole UUID und Timestamp direkt aus der Datenbank
        $db = Database::getInstance();
        $file = $db->prepare('SELECT uuid, tstamp FROM tl_files WHERE path = ?')
            ->execute($path);
        
        if ($file->numRows > 0) {
            return [
                'uuid' => $file->uuid,
                'tstamp' => $file->tstamp,
                'path' => $path,
            ];
        }
        
        return null;
    }
    
    /**
     * Findet Grundriss-Bild anhand Objektnummer
     */
    protected function findImageGrundrissByObjektnummer($objektnummer)
    {
        $cleanNummer = preg_replace('/[^0-9.]/', '', $objektnummer);
        $path = 'files/apartments/visualGrundriss/';
        
        // Suche nach jpg, jpeg oder png
        $extensions = ['jpg', 'jpeg', 'png'];
        
        foreach ($extensions as $ext) {
            $filePath = $path . $cleanNummer . '.' . $ext;
            
            $db = Database::getInstance();
            $file = $db->prepare('SELECT uuid, tstamp FROM tl_files WHERE path = ?')
                ->execute($filePath);
            
            if ($file->numRows > 0) {
                return [
                    'uuid' => $file->uuid,
                    'tstamp' => $file->tstamp,
                    'path' => $filePath,
                ];
            }
        }
        
        return null;
    }
    
    /**
     * Findet Etagen-Bild anhand Objektnummer
     */
    protected function findImageEtageByObjektnummer($objektnummer)
    {
        $cleanNummer = preg_replace('/[^0-9.]/', '', $objektnummer);
        $path = 'files/apartments/visualEtage/';

        // Suche nach jpg, jpeg oder png
        $extensions = ['jpg', 'jpeg', 'png'];

        foreach ($extensions as $ext) {
            $filePath = $path . $cleanNummer . '.' . $ext;

            $db = Database::getInstance();
            $file = $db->prepare('SELECT uuid, tstamp FROM tl_files WHERE path = ?')
                ->execute($filePath);

            if ($file->numRows > 0) {
                return [
                    'uuid' => $file->uuid,
                    'tstamp' => $file->tstamp,
                    'path' => $filePath,
                ];
            }
        }

        return null;
    }

    /**
     * Findet Etagen-Detail-Bild anhand Objektnummer (aus visualEtageDetails)
     */
    protected function findImageEtageDetailByObjektnummer($objektnummer)
    {
        $cleanNummer = preg_replace('/[^0-9.]/', '', $objektnummer);
        $path = 'files/apartments/visualEtageDetails/';

        $extensions = ['jpg', 'jpeg', 'png'];

        foreach ($extensions as $ext) {
            $filePath = $path . $cleanNummer . '.' . $ext;

            $db = Database::getInstance();
            $file = $db->prepare('SELECT uuid, tstamp FROM tl_files WHERE path = ?')
                ->execute($filePath);

            if ($file->numRows > 0) {
                return [
                    'uuid' => $file->uuid,
                    'tstamp' => $file->tstamp,
                    'path' => $filePath,
                ];
            }
        }

        return null;
    }
    
    /**
     * Normalisiert Objektnummer für Vergleich (entfernt Klammern und Inhalt)
     */
    protected function normalizeObjektnummer($objektnummer)
    {
        // Entferne alles in Klammern inkl. führende Leerzeichen
        $normalized = preg_replace('/\s*\([^)]*\)/', '', $objektnummer);
        // Trimmen
        return trim($normalized);
    }
    
    /**
     * Normalisiert Werte für Vergleich (entfernt Leerzeichen, macht lowercase)
     */
    protected function normalizeValue($value)
    {
        // Trimmen
        $value = trim((string)$value);
        
        // Leere Werte einheitlich behandeln
        if ($value === '' || $value === null) {
            return '';
        }
        
        // In lowercase konvertieren
        $value = strtolower($value);
        
        // Mehrfache Leerzeichen entfernen
        $value = preg_replace('/\s+/', ' ', $value);
        
        return $value;
    }
    
    protected function saveTempFile($tmpFile)
    {
        $tempDir = System::getContainer()->getParameter('kernel.project_dir') . '/system/tmp';
        if (!is_dir($tempDir)) {
            mkdir($tempDir, 0755, true);
        }
        
        $tempFile = $tempDir . '/apartments_import_' . uniqid() . '.xlsx';
        copy($tmpFile, $tempFile);
        
        return basename($tempFile);
    }
    
    protected function processImport()
    {
        $tempFileName = Input::post('temp_file');
        $selectedSheets = Input::post('selected_sheets');
        
        if (!$tempFileName) {
            Message::addError('Keine Importdatei gefunden.');
            return;
        }
        
        if (empty($selectedSheets)) {
            Message::addError('Keine Tabs ausgewählt.');
            return;
        }
        
        // Wenn selected_sheets als String kommt (aus hidden input), in Array umwandeln
        if (is_string($selectedSheets)) {
            $selectedSheets = explode(',', $selectedSheets);
        }
        
        $tempFile = System::getContainer()->getParameter('kernel.project_dir') . '/system/tmp/' . $tempFileName;
        
        if (!file_exists($tempFile)) {
            Message::addError('Temporäre Datei nicht gefunden.');
            return;
        }
        
        try {
            $spreadsheet = IOFactory::load($tempFile);
            
            $db = Database::getInstance();
            $imported = 0;
            $updated = 0;
            $skipped = 0;
            
            // Nur ausgewählte Sheets durchgehen
            foreach ($spreadsheet->getAllSheets() as $worksheet) {
                $sheetName = $worksheet->getTitle();
                
                // Skip wenn nicht ausgewählt
                if (!in_array($sheetName, $selectedSheets)) {
                    continue;
                }
                
                // Zeilen durchgehen (Zeile 5 = erste Datenzeile)
                foreach ($worksheet->getRowIterator(5) as $row) {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);
                    
                    $data = [];
                    $colIndex = 0;
                    foreach ($cellIterator as $cell) {
                        // Bruttomietzins (Spalte 10) kann eine Formel sein - berechnet auslesen
                        $data[] = ($colIndex === 10) ? $cell->getCalculatedValue() : $cell->getValue();
                        $colIndex++;
                    }

                    $objektnummer = trim((string)($data[0] ?? ''));

                    if (empty($objektnummer)) {
                        $skipped++;
                        continue;
                    }
                    
                    // Normalisierte Version für Vergleich
                    $objektnummerNormalized = $this->normalizeObjektnummer($objektnummer);
                    
                    // Prüfen ob Wohnung bereits existiert (mit normalisiertem Vergleich)
                    $existing = $db->execute('SELECT * FROM tl_apartments');
                    $existingMatch = null;
                    
                    while ($existing->next()) {
                        if ($this->normalizeObjektnummer($existing->objektnummer) === $objektnummerNormalized) {
                            $existingMatch = $existing->row();
                            break;
                        }
                    }
                    
                    $apartmentData = [
                        'tstamp' => time(),
                        'objektnummer' => trim((string)$objektnummer),
                        'bezeichnung' => trim((string)($data[1] ?? '')),
                        'bauetappe' => trim((string)($data[2] ?? '')),
                        'zeile' => trim((string)($data[3] ?? '')),
                        'adresse' => trim((string)($data[4] ?? '')),
                        'etage' => trim((string)($data[5] ?? '')),
                        'zimmer' => trim((string)($data[6] ?? '')),
                        'flaeche' => trim((string)($data[7] ?? '')),
                        'nettomietzins' => trim((string)($data[8] ?? '')),
                        'nebenkosten' => trim((string)($data[9] ?? '')),
                        'bruttomietzins' => trim((string)($data[10] ?? '')),
                        'status' => 'Frei', // Nur für neue Einträge
                        'published' => true,
                    ];
                    
                    if ($existingMatch !== null) {
                        // Prüfe ob es tatsächlich Änderungen gibt (Status und Dateien ausschließen)
                        $hasChanges = false;
                        
                        foreach ($apartmentData as $field => $newValue) {
                            // Status, tstamp, published und Dateien nicht bei Änderungsprüfung berücksichtigen
                            if (in_array($field, ['tstamp', 'published', 'status', 'grundrisspdf', 'imagegrundriss', 'imageetage'])) {
                                continue;
                            }
                            
                            $oldValue = $existingMatch[$field] ?? '';
                            
                            if ($this->normalizeValue((string)$oldValue) !== $this->normalizeValue((string)$newValue)) {
                                $hasChanges = true;
                                break;
                            }
                        }
                        
                        if ($hasChanges) {
                            // Status und Dateien aus apartmentData entfernen bevor UPDATE
                            unset($apartmentData['status']);
                            unset($apartmentData['grundrisspdf']);
                            unset($apartmentData['imagegrundriss']);
                            unset($apartmentData['imageetage']);
                            
                            // Update nur wenn Änderungen vorhanden
                            $db->prepare('UPDATE tl_apartments %s WHERE id = ?')
                                ->set($apartmentData)
                                ->execute($existingMatch['id']);
                            $updated++;
                        }
                    } else {
                        // Insert - Status wird auf "Frei" gesetzt, Dateien bleiben NULL
                        $db->prepare('INSERT INTO tl_apartments %s')
                            ->set($apartmentData)
                            ->execute();
                        $imported++;
                    }
                }
            }
            
            // Temp-Datei löschen
            @unlink($tempFile);
            
            $total = $imported + $updated + $skipped;
            
            Message::addConfirmation(sprintf(
                'Wohnungen importiert: %d neu, %d aktualisiert, %d übersprungen von insgesamt %d Zeilen.',
                $imported,
                $updated,
                $skipped,
                $total
            ));
            
        } catch (\Exception $e) {
            Message::addError('Fehler beim Import: ' . $e->getMessage());
            
            $logger = System::getContainer()->get('monolog.logger.contao');
            if ($logger instanceof LoggerInterface) {
                $logger->error('Apartments Import Error: ' . $e->getMessage(), ['exception' => $e]);
            }
        }
    }
}