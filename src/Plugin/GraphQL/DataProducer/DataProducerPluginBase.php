<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer;

use Drupal\Component\Plugin\Exception\ContextException;
use Drupal\Core\Plugin\ContextAwarePluginBase;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\Plugin\DataProducerPluginInterface;

/**
 * Base class for data producers that resolve fields for queries or mutations.
 */
abstract class DataProducerPluginBase extends ContextAwarePluginBase implements DataProducerPluginInterface {
  use DataProducerPluginCachingTrait;

  /**
   * {@inheritdoc}
   */
  public function getContextDefinitions() {
    $definition = $this->getPluginDefinition();
    return !empty($definition['consumes']) ? $definition['consumes'] : [];
  }

  /**
   * {@inheritdoc}
   *
   * @throws \Drupal\Component\Plugin\Exception\ContextException
   */
  public function getContextDefinition($name) {
    $definitions = $this->getContextDefinitions();
    if (!empty($definitions[$name])) {
      return $definitions[$name];
    }

    throw new ContextException(sprintf("The %s context is not a valid context.", $name));
  }

  /**
   * {@inheritdoc}
   */
  public function resolveField(FieldContext $field) {
    if (!method_exists($this, 'resolve')) {
      throw new \LogicException('Missing data producer resolve method.');
    }

    $context = $this->getContextValues();
    return call_user_func_array(
      [$this, 'resolve'],
      array_values(array_merge($context, [$field]))
    );
  }

}
