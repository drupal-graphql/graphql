<?php

declare(strict_types=1);

namespace Drupal\graphql\GraphQL\Resolver;

use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Resolves by invoking a callback for the field.
 *
 * @deprecated in graphql:5.0.0 and is removed from graphql:6.0.0. Create a
 *    class implementing Drupal\graphql\Plugin\DataProducerPluginInterface and
 *    use ResolverBuilder::produce() instead.
 *
 * @see https://www.drupal.org/node/3576383
 */
class Callback implements ResolverInterface {

  /**
   * Callback constructor.
   *
   * @param callable $callback
   *   The callback.
   */
  public function __construct(
    protected $callback,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function resolve(mixed $value, array $args, ResolveContext $context, ResolveInfo $info, FieldContext $field): mixed {
    $result = ($this->callback)($value, $args, $context, $info, $field);
    return $result;
  }

}
