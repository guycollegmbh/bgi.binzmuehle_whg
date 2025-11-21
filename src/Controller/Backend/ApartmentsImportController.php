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

class ApartmentsImportController extends Backend
{
    public function importApartments()
    {
        // Backend Template laden
        $template = new BackendTemplate('be_apartments_import');
        
        $template->action = Environment::get('request');
        $template->href = System::getReferer();
        $template->title = 'Zurück';
        
        // Wenn Datei hochgeladen wurde
        if (Input::post('FORM_SUBMIT') === 'tl_apartments_import') {
            $this->processImport();
        }
        
        return $template->parse();
    }
    
    protected function processImport()
    {
        $uploadedFile = $_FILES['import_file'] ?? null;
        
        if (!$uploadedFile || $uploadedFile['error'] !== UPLOAD_ERR_OK) {
            Message::addError('Bitte wählen Sie eine Excel-Datei aus.');
            return;
        }
        
        try {
            $spreadsheet = IOFactory::load($uploadedFile['tmp_name']);
            $worksheet = $spreadsheet->getActiveSheet();
            
            $db = Database::getInstance();
            $imported = 0;
            $updated = 0;
            $skipped = 0;
            
            // Zeilen durchgehen (Zeile 1 = Header, ab Zeile 2 = Daten)
            foreach ($worksheet->getRowIterator(2) as $row) {
                $cellIterator = $row->getCellIterator();
                $cellIterator->setIterateOnlyExistingCells(false);
                
                $data = [];
                foreach ($cellIterator as $cell) {
                    $data[] = $cell->getValue();
                }
                
                // Mapping basierend auf Excel-Struktur
                $objektnummer = $data[0] ?? '';
                
                if (empty($objektnummer)) {
                    $skipped++;
                    continue;
                }
                
                // Prüfen ob Wohnung bereits existiert
                $existing = $db->prepare('SELECT id FROM tl_apartments WHERE objektnummer = ?')
                    ->execute($objektnummer);
                
                $apartmentData = [
                    'tstamp' => time(),
                    'objektnummer' => $objektnummer,
                    'bezeichnung' => $data[1] ?? '',
                    'bauetappe' => $data[2] ?? '',
                    'zeile' => $data[3] ?? '',
                    'adresse' => $data[4] ?? '',
                    'etage' => $data[5] ?? '',
                    'zimmer' => $data[6] ?? '',
                    'flaeche' => $data[7] ?? '',
                    'nettomietzins' => $data[8] ?? '',
                    'nebenkosten' => $data[9] ?? '',
                    'bruttomietzins' => $data[10] ?? '',
                    'status' => $data[11] ?? 'Frei',
                    'published' => true,
                ];
                
                if ($existing->numRows > 0) {
                    // Update
                    $db->prepare('UPDATE tl_apartments %s WHERE id = ?')
                        ->set($apartmentData)
                        ->execute($existing->id);
                    $updated++;
                } else {
                    // Insert
                    $db->prepare('INSERT INTO tl_apartments %s')
                        ->set($apartmentData)
                        ->execute();
                    $imported++;
                }
            }
            
            Message::addConfirmation(sprintf(
                'Import erfolgreich! %d neue Wohnungen importiert, %d aktualisiert, %d übersprungen.',
                $imported,
                $updated,
                $skipped
            ));
            
        } catch (\Exception $e) {
            Message::addError('Fehler beim Import: ' . $e->getMessage());
            System::log('Apartments Import Error: ' . $e->getMessage(), __METHOD__, TL_ERROR);
        }
    }
}