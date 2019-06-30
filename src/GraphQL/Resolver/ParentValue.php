<?php

namespace Drupal\graphql\GraphQL\Resolver;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Type\Definition\ResolveInfo;

class ParentValue implements ResolverInterface {

  /**
   * {@inheritdoc}
   */
  public function resolve($value, $args, ResolveContext $context, ResolveInfo $info, FieldContext $field) {
    if ($value instanceof CacheableDependencyInterface) {
      $context->addCacheableDependency($value);
    }

    return $value;
  }

}
