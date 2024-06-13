<?php
declare(strict_types=1);

namespace Snortlin\Bundle\ConfigBundle\Attribute;

#[\Attribute(\Attribute::TARGET_CLASS)]
final readonly class AsSystemConfig
{
    public function __construct(private ?string $key = null)
    {
    }

    public function getKey(): ?string
    {
        return $this->key;
    }
}
