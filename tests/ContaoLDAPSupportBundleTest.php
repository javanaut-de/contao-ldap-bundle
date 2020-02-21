<?php

/*
 * This file is part of Contao LDAP Support Bundle.
 *
 * (c) Javanaut
 *
 * @license LGPL-3.0-or-later
 */

namespace Refulgent\ContaoLDAPSupportBundle\Tests;

use Refulgent\ContaoLDAPSupportBundle\ContaoLDAPSupportBundle;
use PHPUnit\Framework\TestCase;

class LDAPSelfAppBundleTest extends TestCase
{
    public function testCanBeInstantiated()
    {
        $bundle = new ContaoLDAPSupportBundle();

        $this->assertInstanceOf('Refulgent\ContaoLDAPSupportBundle\ContaoLDAPSupportBundle', $bundle);
    }
}
