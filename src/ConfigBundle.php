<?php
declare(strict_types=1);

namespace Snortlin\Bundle\ConfigBundle;

use Snortlin\Bundle\ConfigBundle\Attribute\AsSystemConfig;
use Snortlin\Bundle\ConfigBundle\Cache\Cache;
use Snortlin\Bundle\ConfigBundle\Manager\Manager;
use Snortlin\Bundle\ConfigBundle\Repository\ConfigRepository;
use Symfony\Component\Config\Definition\Configurator\DefinitionConfigurator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Loader\Configurator\ServicesConfigurator;
use Symfony\Component\Finder\Finder;
use Symfony\Component\HttpKernel\Bundle\AbstractBundle;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

class ConfigBundle extends AbstractBundle
{
    #[\Override]
    public function configure(DefinitionConfigurator $definition): void
    {
        $definition->import('../config/definition.php');
    }

    /**
     * @throws \ReflectionException
     */
    #[\Override]
    public function loadExtension(array $config, ContainerConfigurator $container, ContainerBuilder $builder): void
    {
        $container->import('../config/services/services.php');

        $services = $container->services();

        $this->registerConfigs($services, $config['config_paths'], $builder->getParameter('kernel.project_dir'));
        $this->registerCache($services, $config['cache']);
        $this->registerEntity($services, $container, $config);
    }

    /**
     * @throws \ReflectionException
     */
    private function registerConfigs(ServicesConfigurator $services, array $paths, string $projectDir): void
    {
        $managerService = $services->get(Manager::class);

        if (empty($paths)) {
            $paths[] = "$projectDir/src/Config";
        }

        $paths = array_filter(array_unique($paths), fn(string $path): bool => is_dir($path));

        if (!empty($paths)) {
            foreach ($this->findConfigModels($paths) as $configClass => $configKey) {
                $managerService->call('addConfig', [$configClass, $configKey]);
            }
        }
    }

    /**
     * @throws \ReflectionException
     */
    private function findConfigModels(array $paths): \Generator
    {
        $keyFromClassName = fn(string $className): string => str_replace('\\', '_', $className);

        $iterator = Finder::create()
            ->in($paths)
            ->followLinks()
            ->ignoreUnreadableDirs()
            ->files()
            ->name('*.php');

        $includedFiles = [];

        foreach ($iterator as $file) {
            try {
                require_once $file->getRealPath();

                $includedFiles[$file->getRealPath()] = true;
            } catch (\Throwable) {
                continue;
            }
        }

        $sortedClasses = get_declared_classes();
        sort($sortedClasses);

        $sortedInterfaces = get_declared_interfaces();
        sort($sortedInterfaces);

        $declared = [...$sortedClasses, ...$sortedInterfaces];

        foreach ($declared as $configClass) {
            $reflectionClass = new \ReflectionClass($configClass);
            $sourceFile = $reflectionClass->getFileName();

            if (isset($includedFiles[$sourceFile])) {
                if ($attributes = ($reflectionClass->getAttributes(AsSystemConfig::class, \ReflectionAttribute::IS_INSTANCEOF))) {
                    foreach ($attributes as $attribute) {
                        /** @var AsSystemConfig $instance */
                        $instance = $attribute->newInstance();

                        yield $configClass => $instance->key ?? $keyFromClassName($configClass);
                    }
                }
            }
        }
    }

    private function registerCache(ServicesConfigurator $services, array $config): void
    {
        $services
            ->get(Cache::class)
            ->arg('$enabled', $config['enabled'])
            ->arg('$service', $config['service'] ? service($config['service']) : null)
            ->arg('$keyPrefix', $config['key_prefix'])
            ->arg('$lifetime', $config['lifetime']);
    }

    private function registerEntity(ServicesConfigurator $services, ContainerConfigurator $container, array $config): void
    {
        if ($config['entity_class']) {
            $container->import('../config/services/entity.php');

            $services
                ->get(ConfigRepository::class)
                ->arg('$configClass', $config['entity_class']);
        }
    }
}
