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
        // Hole nur veröffentlichte Apartments mit Bezeichnung "Wohnung"
        $apartments = Database::getInstance()
            ->prepare('SELECT * FROM tl_apartments WHERE published = ? AND bezeichnung = ? ORDER BY bauetappe, zeile, objektnummer')
            ->execute(1, 'Wohnung');

        $apartmentsList = [];
        while ($apartments->next()) {
            $apartmentsList[] = $apartments->row();
        }

        $template->set('apartments', $apartmentsList);

        return $template->getResponse();
    }
}