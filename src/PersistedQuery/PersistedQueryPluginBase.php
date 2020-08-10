<?php

namespace Drupal\graphql\PersistedQuery;

use Drupal\Core\Plugin\PluginBase;
use Drupal\graphql\Plugin\PersistedQueryPluginInterface;

abstract class PersistedQueryPluginBase extends PluginBase implements PersistedQueryPluginInterface {

  /**
   * {@inheritDoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritDoc}
   */
  public function setConfiguration(array $configuration) {
    $this->configuration = $configuration + $this->defaultConfiguration();
  }

  /**
   * {@inheritDoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    $plugin_definition = $this->getPluginDefinition();
    return $plugin_definition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    $plugin_definition = $this->getPluginDefinition();
    return isset($plugin_definition['description']) ? $plugin_definition['description'] : '';
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    if (isset($this->configuration['weight'])) {
      return $this->configuration['weight'];
    }
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {
    $this->configuration['weight'] = $weight;
    return $this;
  }

}
