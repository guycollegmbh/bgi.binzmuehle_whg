<?php

declare(strict_types=1);

namespace Guycollegmbh\ApartmentsBundle\Controller;

use Contao\ContentModel;
use Contao\CoreBundle\Controller\ContentElement\AbstractContentElementController;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Twig\FragmentTemplate;
use Contao\Database;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

#[AsContentElement(category: 'apartments', template: 'ce_apartments_list')]
class ApartmentsListController extends AbstractContentElementController
{
    protected function getResponse(FragmentTemplate $template, ContentModel $model, Request $request): Response
    {
        // Hole alle veröffentlichten Apartments
        $apartments = Database::getInstance()
            ->prepare('SELECT * FROM tl_apartments WHERE published = ? ORDER BY bauetappe, zeile, objektnummer')
            ->execute(1);

        $apartmentsList = [];
        while ($apartments->next()) {
            $apartmentsList[] = $apartments->row();
        }

        $template->set('apartments', $apartmentsList);

        return $template->getResponse();
    }
}