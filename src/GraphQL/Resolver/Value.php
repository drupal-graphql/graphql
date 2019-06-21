<?php

namespace Drupal\graphql\GraphQL\Resolver;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Type\Definition\ResolveInfo;

class Value implements ResolverInterface {

  /**
   * Value to be resolved.
   *
   * @var mixed
   */
  protected $value;

  /**
   * Constructor.
   *
   * @param mixed $value
   *   Value.
   */
  public function __construct($value) {
    $this->value = $value;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($parent, $args, ResolveContext $context, ResolveInfo $info) {
    if ($this->value instanceof CacheableDependencyInterface) {
      $context->addCacheableDependency($this->value);
    }

    return $this->value;
  }

}
