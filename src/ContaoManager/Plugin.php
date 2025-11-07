<?php

declare(strict_types=1);

/*
 * This file is part of Apartments Bundle.
 *
 * (c) GUYCOLLE GMBH / Patrick Grob
 *
 * @license LGPL-3.0-or-later
 */

namespace Guycollegmbh\ApartmentsBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Guycollegmbh\ApartmentsBundle\ApartmentsBundle;

class Plugin implements BundlePluginInterface
{
    public function getBundles(ParserInterface $parser): array
    {
        return [
            BundleConfig::create(ApartmentsBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class]),
        ];
    }
}
