<?php

declare(strict_types=1);

namespace Drupal\graphql\Attribute;

use Drupal\Component\Plugin\Attribute\Plugin;

/**
 * Attribute for persisted query plugins.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class PersistedQuery extends Plugin {

  public function __construct(
    public readonly string $id,
    public readonly string|\Stringable $label,
    public readonly string|\Stringable $description = '',
    public readonly ?string $deriver = NULL,
  ) {
  }

}
