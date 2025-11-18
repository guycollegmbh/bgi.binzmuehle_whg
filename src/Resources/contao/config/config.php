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

// Backend modules
$GLOBALS['BE_MOD']['binzmuehle'] = [
    'apartments' => [
        'tables' => ['tl_apartments', 'tl_zielgruppen', 'tl_zuweisendestellen', 'tl_angebotstypen'],
    ],
];

// Frontend modules
$GLOBALS['FE_MOD']['miscellaneous']['apartments'] = ApartmentsController::class;


