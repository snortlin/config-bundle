<?php

namespace Snortlin\Bundle\ConfigBundle\Exception;

use Snortlin\Bundle\ConfigBundle\Attribute\AsSystemConfig;

final class ConfigNotFoundException extends \RuntimeException
{
    public static function createFromConfigClass(string $configClass): self
    {
        return new self(sprintf('Configuration class "%s" is not registered. Did you forget to add the "%s" attribute?', $configClass, AsSystemConfig::class));

    }

    public static function createFromConfigKey(string $configKey): self
    {
        return new self(sprintf('Configuration with key "%s" is not registered. Did you forget to add the "%s" attribute?', $configKey, AsSystemConfig::class));
    }
}
