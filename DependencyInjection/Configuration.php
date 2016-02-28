<?php
/*
 * This file is part of the SSPGuardBundle.
 *
 * (c) Sergio Gómez <sergio@uco.es>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */


namespace Sgomez\Bundle\SSPGuardBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{

    /**
     * Generates the configuration tree builder.
     *
     * @return \Symfony\Component\Config\Definition\Builder\TreeBuilder The tree builder
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('ssp_guard');

        $rootNode
            ->children()
            ->scalarNode('installation_path')
                ->defaultValue('/var/simplesamlphp')
                ->cannotBeEmpty()
            ->end()
            ->arrayNode('auth_sources')
                ->prototype('array')
                    ->prototype('variable')->end()
                ->end()
            ->end()
        ->end();

        return $treeBuilder;
    }
}