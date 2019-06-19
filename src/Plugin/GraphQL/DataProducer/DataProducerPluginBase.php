<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer;

use Drupal\Component\Plugin\Exception\ContextException;
use Drupal\Core\Plugin\ContextAwarePluginBase;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\Plugin\DataProducerPluginInterface;

abstract class DataProducerPluginBase extends ContextAwarePluginBase implements DataProducerPluginInterface {
  use DataProducerPluginCachingTrait;

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration + $this->defaultConfiguration();
  }

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
  public function resolveInContext(FieldContext $field) {
    if (!method_exists($this, 'resolve')) {
      throw new \LogicException('Missing data producer resolve method.');
    }

    // TODO: The field context should probably be the first argument.
    $context = $this->getContextValues();
    return call_user_func_array([$this, 'resolve'], array_merge($context, [$field]));
  }

}
