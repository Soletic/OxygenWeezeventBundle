<?php

namespace Oxygen\WeezeventBundle\DependencyInjection;

use Oxygen\FrameworkBundle\DependencyInjection\OxygenConfiguration;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration extends OxygenConfiguration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('oxygen_weezevent');

        // Here you should define the parameters that are allowed to
        // configure your bundle. See the documentation linked above for
        // more information on that topic.
        $rootNode->children()->scalarNode('apikey')->cannotBeEmpty()->end()->end();
        $rootNode
        	->children()
        		->arrayNode('default')
        		->cannotBeEmpty()
        			->children()
        				->scalarNode('username')->defaultNull()->end()
        				->scalarNode('password')->defaultNull()->end()
       				->end()
        		->end()
       		->end();

        		
        return $treeBuilder;
    }
}
