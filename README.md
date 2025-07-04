# Application config bundle

Installation
------------

### Step 1: Download the Bundle

The preferred method of installation is via [Composer](https://getcomposer.org/):

```bash
composer require snortlin/config-bundle
```

### Step 2: Enable the Bundle

Then, enable the bundle by adding it to the list of registered bundles
in the `config/bundles.php` file of your project:

```php
// config/bundles.php

return [
    // ...
    Snortlin\Bundle\ConfigBundle\ConfigBundle::class => ['all' => true],
];
```

Usage
-----

```yaml
# config/packages/config.yaml

# Caching is optional
framework:
    cache:
        pools:
            config.cache.config:
                adapter: 'cache.adapter.filesystem'

config:
    entity_class: App\Entity\Config
    cache:
        enabled: true                   # optional, default is true
        service: 'config.cache.config'  # optional, null = Array Cache Adapter
        lifetime: 180                   # optional, null = cache pool default
```

### Storing in database

Create entity:

```php
// App\Entity\Config

use Doctrine\ORM\Mapping as ORM;
use Snortlin\Bundle\ConfigBundle\Entity\AbstractConfig;

#[ORM\Entity]
#[ORM\Table(
    name: 'configs',
)]
class Config extends AbstractConfig
{
}
```

Doctrine migrations:

```php
// DoctrineMigrations

public function up(Schema $schema): void
{
    $this->addSql(<<<'SQL'
        CREATE TABLE configs (
            key VARCHAR(128) NOT NULL,
            value JSON DEFAULT NULL,
            description TEXT DEFAULT NULL,
            PRIMARY KEY(key))
        SQL
    );
    $this->addSql('ALTER TABLE configs ADD CONSTRAINT uc_configs_key UNIQUE(key)');
}
```

### Configuration model

Use the `#[AsSystemConfig]` attribute to define a configuration model and `SerializerFactory::SERIALIZER_GROUP` to define values for serialization.

```php
// App\Config

use Snortlin\Bundle\ConfigBundle\Attribute\AsSystemConfig;
use Snortlin\Bundle\ConfigBundle\Serializer\SerializerFactory;
use Symfony\Component\Serializer\Annotation as Serializer;

#[AsSystemConfig('my_config_key')]
#[Serializer\Groups(SerializerFactory::SERIALIZER_GROUP)]
class MyConfig
{
    public function __construct(public string  $value1,
                                public ?string $value2 = null)
    {
    }

    // ...
}
```

### Configuration defaults

Use `ConfigDefaultsInterface` to implement the default instance.

```php
use Snortlin\Bundle\ConfigBundle\Config\ConfigDefaultsInterface;

class HealthcheckRequestMatcherConfig implements ConfigDefaultsInterface
{
    public static function getConfigDefaults(): self
    {
        return new self('My value1');
    }
}
```

### Configuration defaults

Use `ConfigSerializerContextInterface` to modify serializer context for this model.

```php
use Snortlin\Bundle\ConfigBundle\Config\ConfigSerializerContextInterface;
use Symfony\Component\Serializer\Context\Normalizer\AbstractObjectNormalizerContextBuilder;

class HealthcheckRequestMatcherConfig implements ConfigSerializerContextInterface
{
    public static function withConfigNormalizationContextBuilder(AbstractObjectNormalizerContextBuilder $contextBuilder): AbstractObjectNormalizerContextBuilder
    {
        return $contextBuilder
            ->withGroups(['group1', 'group2']);
    }
}
```

### Avoiding cache key collisions when using a shared cache

```yaml
# config/packages/config.yaml

config:
    cache:
        service: 'cache.app'     # shared cache
        key_prefix: 'configs_'   # cache key prefix
```

### Shared cache

```yaml
# config/packages/config.yaml

config:
    config_paths:
        - '%kernel.project_dir%/src/Config' # this is default
```

### Custom service with namespace prefix

```yaml
# config/packages/config.yaml
services:
    config.cache.config:
        parent: 'cache.adapter.redis_tag_aware'
        tags:
            - { name: 'cache.pool', namespace: '%env(APP_PREFIX_SEED)%_config' }
```
