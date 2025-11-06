<?php

declare(strict_types=1);

namespace guycollegmbh\ServicesBundle\Module;

use Contao\Module; // Add this import
use Contao\BackendTemplate;
use Contao\FrontendTemplate;
use Contao\System;
use Contao\StringUtil;

class ServicesModule extends Module
{
    protected $strTemplate = 'mod_services';

    public function generate()
    {
        if (System::getContainer()->get('contao.routing.scope_matcher')->isBackendRequest(System::getContainer()->get('request_stack')->getCurrentRequest())) {
            $template = new BackendTemplate('be_wildcard');
            $template->wildcard = '### LERNWERK ANGEBOTE ###';
            $template->title = $this->headline;
            $template->id = $this->id;
            $template->link = $this->name;
            $template->href = 'contao/main.php?do=themes&amp;table=tl_module&amp;act=edit&amp;id=' . $this->id;

            return $template->parse();
        }

        return parent::generate();
    }

    protected function compile()
    {
        $objData = $this->Database->prepare("SELECT * FROM tl_services")->execute();
        $this->Template->services = $objData->fetchAllAssoc();
    }
}