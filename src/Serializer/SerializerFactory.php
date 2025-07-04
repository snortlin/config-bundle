<?php
declare(strict_types=1);

namespace Snortlin\Bundle\ConfigBundle\Serializer;

use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\Serializer\Mapping\Loader\AttributeLoader;
use Symfony\Component\Serializer\NameConverter\MetadataAwareNameConverter;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\ArrayDenormalizer;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Normalizer\PropertyNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class SerializerFactory
{
    public const string SERIALIZER_GROUP = 'system:config';

    protected static array $defaultContext = [
        AbstractNormalizer::GROUPS => [self::SERIALIZER_GROUP],
        AbstractObjectNormalizer::SKIP_NULL_VALUES => true,
    ];

    public function __invoke(): SerializerInterface
    {
        return new Serializer($this->getNormalizers());
    }

    protected function getNormalizers(): array
    {
        return [
            new DateTimeNormalizer(),
            new ArrayDenormalizer(),
            $this->createGetSetMethodNormalizer(),
            $this->createPropertyNormalizer(),
        ];
    }

    protected function createGetSetMethodNormalizer(): GetSetMethodNormalizer
    {
        return new GetSetMethodNormalizer(
            classMetadataFactory: $classMetadataFactory = new ClassMetadataFactory(new AttributeLoader()),
            nameConverter: new MetadataAwareNameConverter($classMetadataFactory),
            defaultContext: static::$defaultContext
        );
    }

    protected function createPropertyNormalizer(): PropertyNormalizer
    {
        return new PropertyNormalizer(
            classMetadataFactory: $classMetadataFactory = new ClassMetadataFactory(new AttributeLoader()),
            nameConverter: new MetadataAwareNameConverter($classMetadataFactory),
            defaultContext: [...static::$defaultContext, ...[PropertyNormalizer::NORMALIZE_VISIBILITY => PropertyNormalizer::NORMALIZE_PUBLIC]]
        );
    }
}
