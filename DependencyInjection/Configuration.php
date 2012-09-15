<?php

namespace Tom32i\UserBundle\DependencyInjection;

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
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('tom32i_user');

        $rootNode->
            children()
                ->arrayNode('twitter')
                    ->scalarNode('consumer_key')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('consumer_secret')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('access_token')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                    ->scalarNode('access_token_secret')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                ->end()
                ->arrayNode('mail')
                    ->scalarNode('subject_prefix')
                        ->isRequired()
                        ->cannotBeEmpty()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
