<?php

namespace Btn\MailerBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

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
                ->scalarNode('from_name')->end()
                ->scalarNode('from_email')->isRequired()->end()
                ->variableNode('context')->defaultValue(array())->end()
                ->variableNode('to_email')->end()
                ->arrayNode('templates')
                    ->prototype('array')
                        ->children()
                            ->scalarNode('name')->end()
                            ->scalarNode('template')->isRequired()->end()
                            ->scalarNode('from_name')->end()
                            ->scalarNode('from_email')->end()
                            ->variableNode('context')->defaultValue(array())->end()
                            ->variableNode('to_email')->end()
                            ->arrayNode('context_fields')
                                ->prototype('array')
                                    ->children()
                                        ->scalarNode('name')->end()
                                        ->scalarNode('param_converter')->end()
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
