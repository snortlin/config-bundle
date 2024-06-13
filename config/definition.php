<?php
declare(strict_types=1);

use Psr\Cache\CacheItemPoolInterface;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;

return static function (DefinitionConfigurator $definition): void {
    $definition
        ->rootNode()
            ->children()
                ->arrayNode('config_paths')
                    ->prototype('scalar')->end()
                ->end()
                ->scalarNode('entity_class')->defaultNull()->end()
                ->arrayNode('cache')
                    ->addDefaultsIfNotSet()
                    ->children()
                        ->scalarNode('enabled')->defaultTrue()->end()
                        ->scalarNode('service')
                            ->defaultNull()
                            ->info(sprintf('A cache pool service id that implements "%s"', CacheItemPoolInterface::class))
                        ->end()
                        ->scalarNode('key_prefix')->defaultValue('')->end()
                        ->integerNode('lifetime')->defaultNull()->end()
                    ->end()
                ->end()
            ->end()
        ->end();
};
