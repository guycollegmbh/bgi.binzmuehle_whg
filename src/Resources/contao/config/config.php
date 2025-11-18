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



// Frontend modules
$GLOBALS['FE_MOD']['miscellaneous']['apartments'] = ApartmentsController::class;


// Backend-Stylesheet einbinden
if (defined('TL_MODE') && TL_MODE === 'BE') {
    $GLOBALS['TL_CSS'][] = 'bundles/guycollegmbhapartments/css/backend.css|static';
}