<?php
declare(strict_types=1);

namespace Snortlin\Bundle\ConfigBundle\Manager;

use Snortlin\Bundle\ConfigBundle\Exception\ConfigNotFoundException;

final class Manager
{
    private array $configsByClass = [];
    private array $configsByKeys = [];

    public function getConfigClasses(): array
    {
        return $this->configsByKeys;
    }

    /**
     * @template T
     * @param class-string<T> $configClass
     */
    public function addConfig(string $configClass, string $key): void
    {
        $this->configsByClass[$configClass] = $key;
        $this->configsByKeys[$key] = $configClass;
    }

    public function hasConfigKey(string $configKey): bool
    {
        return array_key_exists($configKey, $this->configsByKeys);
    }

    /**
     * @template T
     * @param class-string<T> $configClass
     * @throws ConfigNotFoundException
     */
    public function getConfigKey(string $configClass): string
    {
        return $this->configsByClass[$configClass] ?? throw ConfigNotFoundException::createFromConfigClass($configClass);
    }

    /**
     * @template T
     * @param class-string<T> $configClass
     */
    public function hasConfigClass(string $configClass): bool
    {
        return array_key_exists($configClass, $this->configsByClass);
    }

    /**
     * @template T
     * @return class-string<T>
     * @throws ConfigNotFoundException
     */
    public function getConfigClass(string $configKey): string
    {
        return $this->configsByKeys[$configKey] ?? throw ConfigNotFoundException::createFromConfigKey($configKey);
    }
}
