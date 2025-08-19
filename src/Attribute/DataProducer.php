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
    public readonly string|\Stringable $name,
    public readonly string|\Stringable $description = '',
    public readonly ?ContextDefinition $produces = NULL,
    public readonly array $consumes = [],
    public readonly ?string $deriver = NULL,
  ) {
  }

}
