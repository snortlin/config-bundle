<?php
declare(strict_types=1);

namespace Snortlin\Bundle\ConfigBundle\Provider;

use Psr\Cache\InvalidArgumentException;
use Snortlin\Bundle\ConfigBundle\Cache\Cache;
use Snortlin\Bundle\ConfigBundle\Config\ConfigDefaultsInterface;
use Snortlin\Bundle\ConfigBundle\Config\ConfigSerializerContextInterface;
use Snortlin\Bundle\ConfigBundle\Manager\Manager;
use Snortlin\Bundle\ConfigBundle\Repository\ConfigRepository;
use Symfony\Component\Serializer\Context\Normalizer\GetSetMethodNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class Provider
{
    public function __construct(private Cache               $cache,
                                private Manager             $manager,
                                private SerializerInterface $serializer,
                                private ?ConfigRepository   $repository)
    {
    }

    /**
     * @template T
     * @param class-string<T> $configClass
     * @throws InvalidArgumentException
     */
    public function get(string $configClass): object
    {
        return $this->cache->getItem(
            $configClass,
            fn(string $configClass): object => $this->getData($configClass)
        );
    }

    /**
     * @template T
     * @param class-string<T> $configClass
     * @throws InvalidArgumentException
     */
    private function getData(string $configClass): object
    {
        if (!$this->repository || null === ($entity = $this->repository->findOneByKey($this->manager->getConfigKey($configClass)))) {
            if (is_subclass_of($configClass, ConfigDefaultsInterface::class)) {
                return $configClass::getConfigDefaults();
            }

            throw new \RuntimeException(sprintf('You are trying to get configuration that is not saved, but the config class "%s" does not implement "%s" interface.', $configClass, ConfigDefaultsInterface::class));
        }

        $value = $entity->getValue();

        $context = [];

        if (is_subclass_of($configClass, ConfigSerializerContextInterface::class)) {
            $context = $configClass::withConfigNormalizationContextBuilder(new GetSetMethodNormalizerContextBuilder())->toArray();
        }

        return $this->serializer->denormalize(data: $value, type: $configClass, context: $context);
    }
}
