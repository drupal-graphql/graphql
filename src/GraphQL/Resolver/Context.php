<?php

declare(strict_types=1);

namespace Drupal\graphql\GraphQL\Resolver;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Resolves a context value with default value support.
 */
class Context implements ResolverInterface {

  /**
   * Context constructor.
   *
   * @param string $name
   *   Name of the context.
   * @param mixed $default
   *   An arbitrary default value in case the context is not set.
   */
  public function __construct(
    protected string $name,
    protected mixed $default = NULL,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function resolve(mixed $value, array $args, ResolveContext $context, ResolveInfo $info, FieldContext $field): mixed {
    $output = $field->getContextValue($this->name);
    if (!isset($output) && !$field->hasContextValue($this->name)) {
      $output = $this->default;
    }

    if ($output instanceof CacheableDependencyInterface) {
      $context->addCacheableDependency($output);
    }

    return $output;
  }

}
