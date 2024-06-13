<?php
declare(strict_types=1);

namespace Symfony\Component\DependencyInjection\Loader\Configurator;

use Doctrine\Persistence\ManagerRegistry;
use Snortlin\Bundle\ConfigBundle\Repository\ConfigRepository;

return static function (ContainerConfigurator $container): void {
    $services = $container->services();

    // Repository

    $services
        ->set(ConfigRepository::class)
        ->args([
            service(ManagerRegistry::class),
            abstract_arg('Config entity class'),
        ]);
};
