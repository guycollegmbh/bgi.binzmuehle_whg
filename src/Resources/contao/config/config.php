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
        'tables' => ['tl_apartments'],
    ],
];

// Frontend modules
$GLOBALS['TL_CTE']['apartments']['apartments_list'] = \Guycollegmbh\ApartmentsBundle\Controller\ContentElement\ApartmentsListController::class;


// Backend-Stylesheet
$GLOBALS['TL_CSS']['apartments_backend'] = 'bundles/apartments/css/backend.css|static';