<?php

namespace Drupal\graphql\Plugin;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Plugin\DerivativeInspectionInterface;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\graphql\GraphQL\Execution\FieldContext;

interface DataProducerPluginInterface extends ContextAwarePluginInterface, CacheableDependencyInterface, DerivativeInspectionInterface {

  /**
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $field
   *
   * @return \GraphQL\Deferred|mixed
   */
  public function resolveField(FieldContext $field);

}
