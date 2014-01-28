<?php

namespace Btn\MailerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('btn_mailer');

        $rootNode
            ->children()
                ->scalarNode('service')->end()
                ->scalarNode('domain')->end()
                ->scalarNode('fromName')->end()
                ->scalarNode('fromEmail')->isRequired()->end()
                ->variableNode('context')->defaultValue(array())->end()
                ->variableNode('toEmail')->end()
                ->arrayNode('templates')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->end()
                            ->scalarNode('template')->isRequired()->end()
                            ->scalarNode('fromName')->end()
                            ->scalarNode('fromEmail')->end()
                            ->variableNode('context')->defaultValue(array())->end()
                            ->variableNode('toEmail')->end()
                            ->arrayNode('contextFields')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('name')->end()
                                        ->scalarNode('paramConverter')->end()
                                        ->enumNode('type')
                                            ->isRequired()
                                            ->values(array('entity', 'text', 'integer'))
                                        ->end()
                                        ->variableNode('options')->end()
                                    ->end()
                                ->end()
                        ->end()
                    ->end()
                ->end()
        ;

        return $treeBuilder;
    }
}
