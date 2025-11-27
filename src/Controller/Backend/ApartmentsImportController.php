/**
 * Dateien-Synchronisierung (PDFs + Bilder) mit Timestamp-Check
 */
public function syncPdfs()
{
    $db = Database::getInstance();
    
    // Hole alle Apartments
    $apartments = $db->execute('SELECT id, objektnummer, grundrisspdf, imagegrundriss, imageetage FROM tl_apartments');
    
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
    }
    
    // Detaillierte Message
    $messages = [];
    
    if ($pdfLinked + $pdfUpdated + $pdfUnchanged > 0) {
        $messages[] = sprintf('PDFs: %d verknüpft, %d aktualisiert, %d unverändert, %d nicht gefunden',
            $pdfLinked, $pdfUpdated, $pdfUnchanged, $pdfNotFound);
    }
    
    if ($imgGrundLinked + $imgGrundUpdated + $imgGrundUnchanged > 0) {
        $messages[] = sprintf('Grundriss-Bilder: %d verknüpft, %d aktualisiert, %d unverändert, %d nicht gefunden',
            $imgGrundLinked, $imgGrundUpdated, $imgGrundUnchanged, $imgGrundNotFound);
    }
    
    if ($imgEtageLinked + $imgEtageUpdated + $imgEtageUnchanged > 0) {
        $messages[] = sprintf('Etagen-Bilder: %d verknüpft, %d aktualisiert, %d unverändert, %d nicht gefunden',
            $imgEtageLinked, $imgEtageUpdated, $imgEtageUnchanged, $imgEtageNotFound);
    }
    
    Message::addConfirmation(sprintf(
        'Dateien synchronisiert (%d Wohnungen):<br>%s',
        $total,
        implode('<br>', $messages)
    ));
    
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
    
    // Vergleiche UUIDs
    if (bin2hex($currentUuid) === bin2hex($newFileInfo['uuid'])) {
        return 'unchanged';
    }
    
    // Vergleiche Timestamps - nur aktualisieren wenn neue Datei neuer ist
    if ($newFileInfo['tstamp'] > $currentFileModel->tstamp) {
        $db->prepare("UPDATE tl_apartments SET {$fieldName} = ? WHERE id = ?")
            ->execute($newFileInfo['uuid'], $apartmentId);
        return 'updated';
    }
    
    return 'unchanged';
}

/**
 * Findet Grundriss-Bild anhand Objektnummer
 */
protected function findImageGrundrissByObjektnummer($objektnummer)
{
    $cleanNummer = preg_replace('/[^0-9.]/', '', $objektnummer);
    $path = 'files/apartments/visualGrundriss/';
    
    // Suche nach jpg oder png
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
    
    // Suche nach jpg oder png
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