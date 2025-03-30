<?php

declare(strict_types=1);

namespace Drupal\graphql\GraphQL\Resolver;

use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Resolves by invoking a callback for the field.
 */
class Callback implements ResolverInterface {

  /**
   * The callback.
   *
   * @var callable
   */
  protected $callback;

  /**
   * Callback constructor.
   */
  public function __construct(callable $callback) {
    $this->callback = $callback;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve(mixed $value, array $args, ResolveContext $context, ResolveInfo $info, FieldContext $field): mixed {
    $result = ($this->callback)($value, $args, $context, $info, $field);
    return $result;
  }

}
