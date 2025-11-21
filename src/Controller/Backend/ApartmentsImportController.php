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
use PhpOffice\PhpSpreadsheet\IOFactory;
use Psr\Log\LoggerInterface;

class ApartmentsImportController extends Backend
{
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
        
        // Schritt 2: Bestätigung und Import
        if (Input::post('FORM_SUBMIT') === 'tl_apartments_import_confirm') {
            $this->processImport();
            $template->messages = Message::generate();
        }
        // Schritt 1: Vorschau generieren
        elseif (Input::post('FORM_SUBMIT') === 'tl_apartments_import') {
            $preview = $this->generatePreview();
            if ($preview) {
                $template->preview = $preview;
            }
            $template->messages = Message::generate();
        }
        
        return $template->parse();
    }
    
    protected function generatePreview()
    {
        $uploadedFile = $_FILES['import_file'] ?? null;
        
        if (!$uploadedFile || $uploadedFile['error'] !== UPLOAD_ERR_OK) {
            Message::addError('Bitte wählen Sie eine Excel-Datei aus.');
            return null;
        }
        
        try {
            $spreadsheet = IOFactory::load($uploadedFile['tmp_name']);
            
            $db = Database::getInstance();
            $preview = [
                'new' => [],
                'update' => [],
                'skip' => [],
                'tempFile' => $this->saveTempFile($uploadedFile['tmp_name']),
            ];
            
            // Alle Sheets durchgehen
            foreach ($spreadsheet->getAllSheets() as $worksheet) {
                $sheetName = $worksheet->getTitle();
                
                // Zeilen durchgehen (Zeile 1 = Header, ab Zeile 2 = Daten)
                foreach ($worksheet->getRowIterator(2) as $row) {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);
                    
                    $data = [];
                    foreach ($cellIterator as $cell) {
                        $data[] = $cell->getValue();
                    }
                    
                    $objektnummer = trim((string)($data[0] ?? ''));
                    
                    if (empty($objektnummer)) {
                        // Prüfe ob die Zeile komplett leer ist
                        $hasData = false;
                        foreach ($data as $cell) {
                            if (!empty(trim((string)$cell))) {
                                $hasData = true;
                                break;
                            }
                        }
                        
                        if ($hasData) {
                            $preview['skip'][] = [
                                'reason' => 'Keine Objektnummer (Tab: ' . $sheetName . ')',
                                'data' => $data,
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
                        'status' => trim((string)($data[11] ?? '')) ?: 'Frei',
                    ];
                    
                    // Prüfen ob Wohnung bereits existiert
                    $existing = $db->prepare('SELECT * FROM tl_apartments WHERE objektnummer = ?')
                        ->execute($objektnummer);
                    
                    if ($existing->numRows > 0) {
                        // Update - Zeige was sich ändert
                        $changes = [];
                        $existingData = $existing->row();
                        
                        foreach ($apartmentData as $field => $newValue) {
                            $oldValue = trim((string)($existingData[$field] ?? ''));
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
                                'id' => $existingData['id'],
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
                $spreadsheet->getSheetCount(),
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
     * Normalisiert Werte für Vergleich (entfernt Leerzeichen, macht lowercase)
     */
    protected function normalizeValue($value)
    {
        // Trimmen und in lowercase konvertieren
        $value = strtolower(trim((string)$value));
        
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
        
        if (!$tempFileName) {
            Message::addError('Keine Importdatei gefunden.');
            return;
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
            
            // Alle Sheets durchgehen
            foreach ($spreadsheet->getAllSheets() as $worksheet) {
                // Zeilen durchgehen (Zeile 1 = Header, ab Zeile 2 = Daten)
                foreach ($worksheet->getRowIterator(2) as $row) {
                    $cellIterator = $row->getCellIterator();
                    $cellIterator->setIterateOnlyExistingCells(false);
                    
                    $data = [];
                    foreach ($cellIterator as $cell) {
                        $data[] = $cell->getValue();
                    }
                    
                    $objektnummer = trim((string)($data[0] ?? ''));
                    
                    if (empty($objektnummer)) {
                        $skipped++;
                        continue;
                    }
                    
                    // Prüfen ob Wohnung bereits existiert
                    $existing = $db->prepare('SELECT * FROM tl_apartments WHERE objektnummer = ?')
                        ->execute($objektnummer);
                    
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
                        'status' => trim((string)($data[11] ?? '')) ?: 'Frei',
                        'published' => true,
                    ];
                    
                    if ($existing->numRows > 0) {
                        // Prüfe ob es tatsächlich Änderungen gibt
                        $hasChanges = false;
                        $existingData = $existing->row();
                        
                        foreach ($apartmentData as $field => $newValue) {
                            if ($field === 'tstamp' || $field === 'published') {
                                continue;
                            }
                            
                            $oldValue = $existingData[$field] ?? '';
                            
                            if ($this->normalizeValue((string)$oldValue) !== $this->normalizeValue((string)$newValue)) {
                                $hasChanges = true;
                                break;
                            }
                        }
                        
                        if ($hasChanges) {
                            // Update nur wenn Änderungen vorhanden
                            $db->prepare('UPDATE tl_apartments %s WHERE id = ?')
                                ->set($apartmentData)
                                ->execute($existing->id);
                            $updated++;
                        }
                    } else {
                        // Insert
                        $db->prepare('INSERT INTO tl_apartments %s')
                            ->set($apartmentData)
                            ->execute();
                        $imported++;
                    }
                }
            }
            
            // Temp-Datei löschen
            @unlink($tempFile);
            
            Message::addConfirmation(sprintf(
                'Import erfolgreich! %d neue Wohnungen importiert, %d aktualisiert, %d übersprungen.',
                $imported,
                $updated,
                $skipped
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