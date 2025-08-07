<?php

declare(strict_types=1);

namespace Drupal\graphql\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;
use Drupal\Core\Plugin\Context\ContextDefinition;

/**
 * Attribute for dataproducer plugins.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class DataProducer extends Plugin {

  public function __construct(
    public readonly string $id,
    public readonly string $name,
    public readonly ContextDefinition $produces,
    public readonly string $description = '',
    public readonly array $consumes = [],
  ) {
  }

}
