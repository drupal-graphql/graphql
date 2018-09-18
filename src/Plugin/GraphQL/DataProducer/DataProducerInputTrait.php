<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Type\Definition\ResolveInfo;

trait DataProducerInputTrait {

  /**
   * @param $from
   *
   * @return boolean
   */
  protected function hasInputMapper($from) {
    if (!($this instanceof ConfigurablePluginInterface)) {
      return FALSE;
    }

    return isset($this->getConfiguration()['mapping'][$from]);
  }

  /**
   * @param $from
   *
   * @return callable|null
   */
  protected function getInputMapper($from) {
    return $this->getConfiguration()['mapping'][$from] ?? NULL;
  }

  /**
   * @param $value
   * @param $args
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *
   * @return array|\GraphQL\Deferred
   * @throws \Exception
   */
  protected function getInputValues($value, $args, ResolveContext $context, ResolveInfo $info) {
    $values = [];

    $definitions = $this->getPluginDefinition();
    $consumes = isset($definitions['consumes']) ? $definitions['consumes'] : [];
    foreach ($consumes as $key => $definition) {
      if ($definition->isRequired() && !$this->hasInputMapper($key)) {
        throw new \Exception(sprintf('Missing input data mapper for %s on field %s on type %s.', $key, $info->fieldName, $info->parentType->name));
      }

      $mapper = $this->getInputMapper($key);
      if (isset($mapper) && !is_callable($mapper)) {
        throw new \Exception(sprintf('Invalid input mapper for %s on field %s on type %s. Input mappers need to be callable.', $key, $info->fieldName, $info->parentType->name));
      }

      $values[$key] = isset($mapper) ? $mapper($value, $args, $context, $info) : NULL;
      if ($definition->isRequired() && !isset($values[$key])) {
        throw new \Exception(sprintf('Missing input data for %s on field %s on type %s.', $key, $info->fieldName, $info->parentType->name));
      }
    }

    return $values;
  }

}