<?php

declare(strict_types=1);

use Guycollegmbh\ApartmentsBundle\Controller\FrontendModule\ApartmentsListController;

// Backend modules
$GLOBALS['BE_MOD']['binzmuehle'] = [
    'apartments' => [
        'tables' => ['tl_apartments', 'tl_zielgruppen', 'tl_zuweisendestellen', 'tl_angebotstypen'],
    ],
];

// Frontend modules
$GLOBALS['FE_MOD']['apartments']['apartments_list'] = ApartmentsListController::class;

// Backend-Stylesheet einbinden
$GLOBALS['TL_CSS']['apartments_backend'] = 'bundles/apartments/css/static';