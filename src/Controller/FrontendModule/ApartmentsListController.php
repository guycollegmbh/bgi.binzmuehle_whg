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

        // Bezeichnungen die ausgeblendet werden sollen
        $excludedBezeichnungen = [
            'Jokerzimmer',
            'Atelier',
            'Parkplatz IV',
            'Parkplatz',
            'Moto Hallenplatz',
            'Parkplatz IV E-Mob',
            'Parkplatz E-Mob'
        ];

        $apartmentsList = [];
        while ($apartments->next()) {
            $row = $apartments->row();

            // Filtere ausgeschlossene Bezeichnungen heraus
            if (!in_array($row['bezeichnung'], $excludedBezeichnungen)) {
                $apartmentsList[] = $row;
            }
        }

        $template->set('apartments', $apartmentsList);

        return $template->getResponse();
    }
}