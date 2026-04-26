<?php

declare(strict_types=1);

namespace Drupal\graphql\GraphQL\Resolver;

use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Resolves by forwarding to another resolver.
 */
class Tap implements ResolverInterface {

  /**
   * Tap constructor.
   *
   * @param \Drupal\graphql\GraphQL\Resolver\ResolverInterface $resolver
   *   The wrapped resolver.
   */
  public function __construct(
    protected ResolverInterface $resolver,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public function resolve(mixed $value, array $args, ResolveContext $context, ResolveInfo $info, FieldContext $field): mixed {
    $this->resolver->resolve($value, $args, $context, $info, $field);
    return $value;
  }

}
