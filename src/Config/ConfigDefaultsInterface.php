<?php
declare(strict_types=1);

namespace Snortlin\Bundle\ConfigBundle\Config;

interface ConfigDefaultsInterface
{
    public static function getConfigDefaults(): ConfigDefaultsInterface;
}
