<?php
declare(strict_types=1);

namespace Snortlin\Bundle\ConfigBundle\Cache;

use Psr\Cache\CacheItemPoolInterface;
use Psr\Cache\InvalidArgumentException;
use Snortlin\Bundle\ConfigBundle\Manager\Manager;
use Symfony\Component\Cache\Adapter\ArrayAdapter;
use Symfony\Component\Cache\Adapter\ChainAdapter;
use Symfony\Component\Cache\Adapter\NullAdapter;
use Symfony\Contracts\Cache\ItemInterface;

final readonly class Cache
{
    private CacheItemPoolInterface $cache;

    public function __construct(private Manager                 $manager,
                                bool                            $enabled,
                                private ?CacheItemPoolInterface $service,
                                private string                  $keyPrefix = '',
                                private ?int                    $lifetime = null)
    {
        if (!$enabled) {
            $this->cache = new NullAdapter();
        } elseif ($service instanceof CacheItemPoolInterface) {
            $this->cache = new ChainAdapter([
                new ArrayAdapter(),
                $this->service,
            ]);
        } else {
            $this->cache = new ArrayAdapter();
        }
    }

    /**
     * @template T
     * @param class-string<T> $configClass
     * @return T
     * @throws InvalidArgumentException
     */
    public function getItem(string $configClass, callable $callback): object
    {
        return $this->get($this->manager->getConfigKey($configClass), function (ItemInterface $item) use ($configClass, $callback): object {
            $item->expiresAfter($this->lifetime);

            return $callback($configClass);
        });
    }

    /**
     * @template T
     * @param class-string<T> $configClass
     * @throws InvalidArgumentException
     */
    public function deleteItem(string $configClass): bool
    {
        return $this->delete(
            $this->manager->getConfigKey($configClass)
        );
    }

    public function clear(): bool
    {
        return $this->cache->clear();
    }

    /**
     * @throws InvalidArgumentException
     */
    private function get(string $key, callable $callback): mixed
    {
        return $this->cache->get(
            $this->getCacheKey($key), $callback
        );
    }

    /**
     * @throws InvalidArgumentException
     */
    private function delete(string $key): bool
    {
        return $this->cache->delete(
            $this->getCacheKey($key)
        );
    }

    private function getCacheKey(string $key): string
    {
        return $this->keyPrefix . $key;
    }
}
