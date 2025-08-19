<?php

declare(strict_types=1);

namespace Drupal\graphql\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;

/**
 * Attribute for dataproducer plugins.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class Schema extends Plugin {

  public function __construct(
    public readonly string $id,
    public readonly string|\Stringable $name,
    public readonly string|\Stringable $description = '',
    public readonly ?string $deriver = NULL,
  ) {
  }

}
