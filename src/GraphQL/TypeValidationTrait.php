<?php

namespace Drupal\graphql\GraphQL;

trait TypeValidationTrait {

  /**
   * Retrieves the referenced plugin instance.
   *
   * @return \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface
   *   The referenced plugin instance.
   */
  abstract public function getPlugin();

  /**
   * {@inheritdoc}
   */
  public function isValidValue($value) {
    if (($plugin = $this->getPlugin()) && $plugin instanceof TypeValidationInterface) {
      return $plugin->isValidValue($value);
    }

    return parent::isValidValue($value);
  }

}