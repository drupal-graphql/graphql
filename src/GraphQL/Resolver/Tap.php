<?php

namespace Drupal\graphql\GraphQL\Resolver;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Taps a resolver.
 */
class Tap implements ResolverInterface {

  /**
   * Resolver to tap.
   *
   * @var mixed
   */
  protected $resolver;

  /**
   * Constructor.
   *
   * @param mixed $resolver
   *   Resolver to tap.
   */
  public function __construct($resolver) {
    $this->resolver = $resolver;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($value, $args, ResolveContext $context, ResolveInfo $info) {
    $this->resolver->resolve($value, $args, $context, $info);
    return $value;
  }

}
