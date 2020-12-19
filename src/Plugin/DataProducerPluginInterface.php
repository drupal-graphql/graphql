<?php

namespace Drupal\graphql\Plugin;

use Drupal\Component\Plugin\DerivativeInspectionInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\graphql\GraphQL\Execution\FieldContext;

/**
 * Defines the contract of data producer plugins.
 *
 * Note that this misses a definition of the resolve() method as that has a
 * different signature per plugin.
 */
interface DataProducerPluginInterface extends ContextAwarePluginInterface, CacheableDependencyInterface, DerivativeInspectionInterface {

  /**
   * Resolves the queried field with the given context.
   *
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $field
   *
   * @return \GraphQL\Deferred|mixed
   */
  public function resolveField(FieldContext $field);

}
