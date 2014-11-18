<?php

namespace Bluetea\JiraRestApiBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('bluetea_jira_rest_api');

        $rootNode
            ->children()
                ->scalarNode('api_client')
                    ->defaultValue('curl')
                ->end()
                ->scalarNode('base_url')
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('authentication')
                    ->children()
                        ->enumNode('type')
                            ->defaultValue('basic')
                            ->values(array('basic', 'anonymous'))
                        ->end()
                        ->scalarNode('username')->end()
                        ->scalarNode('password')->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
