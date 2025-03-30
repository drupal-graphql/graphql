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
   * Name of the context.
   */
  protected mixed $name;

  /**
   * An arbitrary default value in case the context is not set.
   */
  protected mixed $default;

  /**
   * Context constructor.
   */
  public function __construct(string $name, mixed $default = NULL) {
    $this->name = $name;
    $this->default = $default;
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
