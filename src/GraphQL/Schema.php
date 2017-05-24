<?php

namespace Drupal\graphql\GraphQL;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyTrait;
use Youshido\GraphQL\Config\Schema\SchemaConfig;
use Youshido\GraphQL\Schema\AbstractSchema;

class Schema extends AbstractSchema implements RefinableCacheableDependencyInterface  {
  use RefinableCacheableDependencyTrait;

  /**
   * {@inheritdoc}
   */
  public function build(SchemaConfig $config) {
    // Not needed in most cases as implementing modules are most likely going to
    // use the add methods to incrementally build the schema.
  }
}
