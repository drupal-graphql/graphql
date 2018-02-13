<?php

namespace Drupal\graphql\GraphQL\Execution\Visitor;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\graphql\GraphQL\CacheableEdgeInterface;
use Youshido\GraphQL\Field\FieldInterface;

class CacheMetadataVisitor implements VisitorInterface {

  /**
   * {@inheritdoc}
   */
  public function visit(array $args, FieldInterface $field, $child) {
    if ($field instanceof CacheableEdgeInterface) {
      $metadata = new CacheableMetadata();
      $metadata->addCacheableDependency($field->getResponseCacheMetadata());
      $metadata->addCacheableDependency($field->getSchemaCacheMetadata());
      return $metadata;
    }
  }

  /**
   * {@inheritdoc}
   */
  public function reduce($carry, $current) {
    /** @var \Drupal\Core\Cache\RefinableCacheableDependencyInterface $carry */
    if ($current instanceof CacheableDependencyInterface) {
      $carry->addCacheableDependency($current);
    }

    return $carry;
  }

  /**
   * {@inheritdoc}
   */
  public function initial() {
    return new CacheableMetadata();
  }

}