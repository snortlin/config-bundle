<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Snortlin\Bundle\ConfigBundle\Cache\Cache;
use Snortlin\Bundle\ConfigBundle\Command\ConfigSetDefaultsCommand;
use Snortlin\Bundle\ConfigBundle\Command\ConfigListCommand;
use Snortlin\Bundle\ConfigBundle\Manager\Manager;
use Snortlin\Bundle\ConfigBundle\Persister\Persister;
use Snortlin\Bundle\ConfigBundle\Provider\Provider;
use Snortlin\Bundle\ConfigBundle\Repository\ConfigRepository;
use Snortlin\Bundle\ConfigBundle\Serializer\SerializerFactory;
use Symfony\Component\Serializer\SerializerInterface;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    // Manager

    $services
        ->set(Manager::class);

    // Cache

    $services
        ->set(Cache::class)
        ->args([
            service(Manager::class),
            abstract_arg('Is Config cache enabled?'),
            abstract_arg('Config cache service'),
            abstract_arg('Config cache key prefix'),
            abstract_arg('Config cache lifetime'),
        ]);

    // Serializer

    $services
        ->set(SerializerFactory::class);

    $services
        ->set('system_config.serializer', SerializerInterface::class)
        ->factory(service(SerializerFactory::class));

    // Provider

    $services
        ->set(Provider::class)
        ->args([
            service(Cache::class),
            service(Manager::class),
            service('system_config.serializer'),
            service(ConfigRepository::class)->nullOnInvalid(),
        ]);

    // Persister

    $services
        ->set(Persister::class)
        ->args([
            service(Cache::class),
            service(Manager::class),
            service('system_config.serializer'),
            service(ConfigRepository::class)->nullOnInvalid(),
        ]);

    // Commands

    $services
        ->set(ConfigListCommand::class)
        ->args([
            service(Manager::class),
        ])
        ->tag('console.command');

    $services
        ->set(ConfigSetDefaultsCommand::class)
        ->args([
            service(Manager::class),
            service(Persister::class),
        ])
        ->tag('console.command');
};
