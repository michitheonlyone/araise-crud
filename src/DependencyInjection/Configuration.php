<?php

declare(strict_types=1);

namespace araise\CrudBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files.
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/configuration.html}
 */
class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder(): TreeBuilder
    {
        $treeBuilder = new TreeBuilder('araise_crud');
        $rootNode = $treeBuilder->getRootNode();

        $rootNode
            ->children()
            ->arrayNode('breadcrumbs')
            ->children()
            ->scalarNode('home_text')
            ->defaultValue('Dashboard')
            ->end()
            ->scalarNode('home_route')
            ->defaultValue('')
            ->end()
            ->end()
            ->end() // end breadcrumbs
            ->arrayNode('templates')
            ->children()
            ->scalarNode('show')
            ->defaultValue('araiseCrudBundle:Crud/_boxes:show.html.twig')
            ->end()
            ->scalarNode('create')
            ->defaultValue('araiseCrudBundle:Crud/_boxes:create.html.twig')
            ->end()
            ->scalarNode('edit')
            ->defaultValue('araiseCrudBundle:Crud/_boxes:edit.html.twig')
            ->end()
            ->end()
            ->end() // end templates
            ->scalarNode('templateDirectory')
            ->defaultValue('@araiseCrud/Crud')
            ->end()
            ->scalarNode('layout')
            ->defaultValue('@araiseCrud/layout/adminlte_layout.html.twig')
            ->end()
            ->end();

        return $treeBuilder;
    }
}
