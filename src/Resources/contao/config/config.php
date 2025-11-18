<?php

declare(strict_types=1);

/*
 * This file is part of Apartments Bundle.
 *
 * (c) GUYCOLLE GMBH / Patrick Grob
 *
 * @license LGPL-3.0-or-later
 */

use Guycollegmbh\ApartmentsBundle\Controller\ContentElement\ApartmentsController;

// TEST - wird diese Datei überhaupt geladen?
error_log('ApartmentsBundle config.php wurde geladen!');

// Backend modules
$GLOBALS['BE_MOD']['binzmuehle'] = [
    'apartments' => [
        'tables' => ['tl_apartments', 'tl_zielgruppen', 'tl_zuweisendestellen', 'tl_angebotstypen'],
    ],
];

// Frontend modules
$GLOBALS['FE_MOD']['miscellaneous']['apartments'] = ApartmentsController::class;


// Backend-Stylesheet einbinden
if (defined('TL_MODE') && TL_MODE === 'BE') {
    $GLOBALS['TL_CSS'][] = 'bundles/guycollegmbhapartments/css/backend.css|static';
}