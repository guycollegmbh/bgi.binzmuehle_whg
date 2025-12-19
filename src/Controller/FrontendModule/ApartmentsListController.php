<?php

declare(strict_types=1);

namespace Guycollegmbh\ApartmentsBundle\Controller\FrontendModule;

use Contao\CoreBundle\Controller\FrontendModule\AbstractFrontendModuleController;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\Database;
use Contao\ModuleModel;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ApartmentsListController extends AbstractFrontendModuleController
{
    protected function getResponse(FragmentTemplate $template, ModuleModel $model, Request $request): Response
    {
        // Hole alle veröffentlichten Apartments
        $apartments = Database::getInstance()
            ->prepare('SELECT * FROM tl_apartments WHERE published = ? ORDER BY bauetappe, zeile, objektnummer')
            ->execute(1);

        // Nur Wohnungen anzeigen (alle anderen Bezeichnungen ausblenden)
        $apartmentsList = [];
        while ($apartments->next()) {
            $row = $apartments->row();

            // Nur Apartments mit Bezeichnung "Wohnung" anzeigen
            $bezeichnung = trim($row['bezeichnung']);
            if (strcasecmp($bezeichnung, 'Wohnung') === 0) {
                $apartmentsList[] = $row;
            }
        }

        $template->set('apartments', $apartmentsList);

        return $template->getResponse();
    }
}