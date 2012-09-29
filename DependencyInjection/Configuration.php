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

        $rootNode
            ->children()
                ->scalarNode('user_class')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('site_name')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->scalarNode('email')
                    ->isRequired()
                    ->cannotBeEmpty()
                ->end()
                ->arrayNode('form')
                    ->children()
                        ->scalarNode('profile')
                            ->defaultValue('tom32i_user.profile.form.type')
                        ->end()
                        ->scalarNode('registration')
                            ->defaultValue('tom32i_user.registration.form.type')
                        ->end()
                    ->end()
                ->end()
                ->arrayNode('twitter')
                    ->children()
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
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
