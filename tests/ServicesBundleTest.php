<?php

declare(strict_types=1);

/*
 * This file is part of Services Bundle.
 *
 * (c) GUYCOLLE GMBH / Patrick Grob
 *
 * @license LGPL-3.0-or-later
 */

namespace guycollegmbh\ServicesBundle\Tests;

use guycollegmbh\ServicesBundle\ServicesBundle;
use PHPUnit\Framework\TestCase;

class ServicesBundleTest extends TestCase
{
    public function testCanBeInstantiated(): void
    {
        $bundle = new ServicesBundle();

        $this->assertInstanceOf('guycollegmbh\ServicesBundle\ServicesBundle', $bundle);
    }
}
