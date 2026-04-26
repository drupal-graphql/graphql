<?php

declare(strict_types=1);

namespace Drupal\graphql\GraphQL\Resolver;

use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Resolves by an argument name lookup.
 */
class Argument implements ResolverInterface {

  /**
   * Argument constructor.
   *
   * @param string $name
   *   The argument name.
   */
  public function __construct(
    protected string $name,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function resolve(mixed $value, array $args, ResolveContext $context, ResolveInfo $info, FieldContext $field): mixed {
    return $args[$this->name] ?? NULL;
  }

}
