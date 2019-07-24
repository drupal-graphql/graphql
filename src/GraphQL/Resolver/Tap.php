<?php

namespace Drupal\graphql\GraphQL\Resolver;

use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Type\Definition\ResolveInfo;

class Tap implements ResolverInterface {

  /**
   * Resolver to tap.
   *
   * @var mixed
   */
  protected $resolver;

  /**
   * Tap constructor.
   *
   * @param \Drupal\graphql\GraphQL\Resolver\ResolverInterface $resolver
   */
  public function __construct(ResolverInterface $resolver) {
    $this->resolver = $resolver;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($value, $args, ResolveContext $context, ResolveInfo $info, FieldContext $field) {
    $this->resolver->resolve($value, $args, $context, $info, $field);
    return $value;
  }

}
