<?php
declare(strict_types=1);

namespace Snortlin\Bundle\ConfigBundle\Entity;

use Symfony\Component\Validator\Constraints as Assert;

abstract class AbstractConfig
{
    #[Assert\NotBlank, Assert\Length(max: 128)]
    private string $key;
    private ?array $value = null;
    private ?string $description = null;

    public function __construct(string $identifier)
    {
        $this->key = $identifier;
    }

    public function getKey(): string
    {
        return $this->key;
    }

    public function setKey(string $key): AbstractConfig
    {
        $this->key = $key;
        return $this;
    }

    public function getValue(): ?array
    {
        return $this->value;
    }

    public function setValue(?array $value): AbstractConfig
    {
        $this->value = $value;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): AbstractConfig
    {
        $this->description = $description;
        return $this;
    }
}
