<?php
declare(strict_types=1);

namespace Snortlin\Bundle\ConfigBundle\Config;

use Symfony\Component\Serializer\Context\Normalizer\AbstractObjectNormalizerContextBuilder;

interface ConfigSerializerContextInterface
{
    public static function withConfigNormalizationContextBuilder(AbstractObjectNormalizerContextBuilder $contextBuilder): AbstractObjectNormalizerContextBuilder;
}
