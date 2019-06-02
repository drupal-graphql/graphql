<?php

namespace Drupal\graphql\GraphQL\Resolver;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Type\Definition\ResolveInfo;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerInterface;

/**
 * Resolves a parent.
 */
class ParentValue implements DataProducerInterface {

  /**
   * {@inheritdoc}
   */
  public function resolve($value, $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof CacheableDependencyInterface) {
      $context->addCacheableDependency($value);
    }

    return $value;
  }

}
