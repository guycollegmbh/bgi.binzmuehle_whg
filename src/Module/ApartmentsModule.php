<?php

declare(strict_types=1);

/*
 * This file is part of Apartments Bundle.
 *
 * (c) GUYCOLLE GMBH / Patrick Grob
 *
 * @license LGPL-3.0-or-later
 */

namespace Guycollegmbh\ApartmentsBundle\Module;

use Contao\BackendTemplate;
use Contao\CoreBundle\DependencyInjection\Attribute\AsContentElement;
use Contao\CoreBundle\Routing\ScopeMatcher;
use Contao\Module;
use Contao\System;
use Doctrine\DBAL\Connection;
use Symfony\Component\HttpFoundation\RequestStack;

#[AsContentElement(category: 'miscellaneous', type: 'apartments')]
class ApartmentsModule extends Module
{
    protected $strTemplate = 'mod_apartments';

    private Connection $connection;
    private ScopeMatcher $scopeMatcher;
    private RequestStack $requestStack;

    public function __construct(Connection $connection, ScopeMatcher $scopeMatcher, RequestStack $requestStack)
    {
        $this->connection = $connection;
        $this->scopeMatcher = $scopeMatcher;
        $this->requestStack = $requestStack;
    }

    public function generate(): string
    {
        $request = $this->requestStack->getCurrentRequest();

        if ($request && $this->scopeMatcher->isBackendRequest($request)) {
            $template = new BackendTemplate('be_wildcard');
            $template->wildcard = '### APARTMENTS MODULE ###';
            $template->title = $this->headline;
            $template->id = $this->id;
            $template->link = $this->name;
            $template->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $template->parse();
        }

        return parent::generate();
    }

    protected function compile(): void
    {
        // Modern Doctrine DBAL query
        $apartments = $this->connection->fetchAllAssociative(
            'SELECT * FROM tl_apartments WHERE published = :published ORDER BY sorting',
            ['published' => 1]
        );

        $this->Template->apartments = $apartments;
    }
}