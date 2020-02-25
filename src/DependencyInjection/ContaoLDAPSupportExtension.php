<?php

/*
 * This file is part of Contao LDAP Support Bundle.
 *
 * (c) Javanaut
 *
 * @license LGPL-3.0-or-later
 */

namespace Refulgent\ContaoLDAPSupportBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class ContaoLDAPSupportExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $mergedConfig, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__.'/../Resources/config')
        );

		die('loader');

        $loader->load('services.yml');
    }
}
