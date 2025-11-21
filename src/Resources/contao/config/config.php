<?php

declare(strict_types=1);

use Guycollegmbh\ApartmentsBundle\Module\ApartmentsListModule;
use Guycollegmbh\ApartmentsBundle\Module\ApartmentsDetailModule;

// Backend modules
$GLOBALS['BE_MOD']['binzmuehle']['apartments'] = [
    'tables' => ['tl_apartments'],
    'import' => ['Guycollegmbh\ApartmentsBundle\Controller\Backend\ApartmentsImportController', 'importApartments'],
];

// Frontend modules
$GLOBALS['FE_MOD']['apartments']['apartments_list'] = ApartmentsListModule::class;
$GLOBALS['FE_MOD']['apartments']['apartments_detail'] = ApartmentsDetailModule::class;

// Backend-Stylesheet einbinden
$GLOBALS['TL_CSS']['apartments_backend'] = 'bundles/apartments/css/backend.css|static';