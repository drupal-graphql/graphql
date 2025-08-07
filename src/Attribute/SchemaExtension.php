<?php

declare(strict_types=1);

namespace Drupal\graphql\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;

/**
 * Attribute for dataproducer plugins.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class SchemaExtension extends Plugin {

  public function __construct(
    public readonly string $id,
    public readonly string $name,
    public readonly string $schema,
    public readonly string $description = '',
    public readonly int $priority = 0,
  ) {
  }

}
