<?php
declare(strict_types=1);

namespace Snortlin\Bundle\ConfigBundle\Persister;

use Psr\Cache\InvalidArgumentException;
use Snortlin\Bundle\ConfigBundle\Cache\Cache;
use Snortlin\Bundle\ConfigBundle\Config\ConfigSerializerContextInterface;
use Snortlin\Bundle\ConfigBundle\Manager\Manager;
use Snortlin\Bundle\ConfigBundle\Repository\ConfigRepository;
use Symfony\Component\Serializer\Context\Normalizer\GetSetMethodNormalizerContextBuilder;
use Symfony\Component\Serializer\SerializerInterface;

final readonly class Persister
{
    public function __construct(private Cache               $cache,
                                private Manager             $manager,
                                private SerializerInterface $serializer,
                                private ?ConfigRepository   $repository)
    {
    }

    /**
     * @throws InvalidArgumentException
     */
    public function set(object $config): void
    {
        if ($this->repository) {
            $key = $this->manager->getConfigKey($config::class);

            if (null === ($entity = $this->repository->findOneByKey($key))) {
                $entityClass = $this->repository->getClassName();
                $entity = new $entityClass($key);
            }

            $context = [];

            if (is_subclass_of($config, ConfigSerializerContextInterface::class)) {
                $context = $config::withConfigNormalizationContextBuilder(new GetSetMethodNormalizerContextBuilder())->toArray();
            }

            $value = $this->serializer->normalize(data: $config, context: $context);

            $entity->setValue($value);

            $this->repository->persist($entity);
        }

        $this->cache->deleteItem($config::class);
    }
}
