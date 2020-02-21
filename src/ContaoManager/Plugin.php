<?php

/*
 * This file is part of Contao LDAP Support Bundle.
 *
 * (c) Javanaut
 *
 * @license LGPL-3.0-or-later
 */

namespace Refulgent\ContaoLDAPSupportBundle\ContaoManager;

use Contao\CoreBundle\ContaoCoreBundle;
use Contao\ManagerPlugin\Bundle\BundlePluginInterface;
use Contao\ManagerPlugin\Bundle\Config\BundleConfig;
use Contao\ManagerPlugin\Bundle\Parser\ParserInterface;
use Refulgent\ContaoLDAPSupportBundle\ContaoLDAPSupportBundle;

class Plugin implements BundlePluginInterface
{
    /**
     * {@inheritdoc}
     */
    public function getBundles(ParserInterface $parser)
    {
        return [
            BundleConfig::create(ContaoLDAPSupportBundle::class)
                ->setLoadAfter([ContaoCoreBundle::class]),
        ];
    }
}
