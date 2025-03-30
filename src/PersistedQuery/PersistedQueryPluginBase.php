<?php

declare(strict_types=1);

namespace Drupal\graphql\PersistedQuery;

use Drupal\Core\Plugin\PluginBase;
use Drupal\graphql\Plugin\PersistedQueryPluginInterface;

/**
 * Base class persisted query plugins that represent a GraphQL persisted query.
 */
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
  public function setConfiguration(array $configuration): void {
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
  public function label(): string {
    $plugin_definition = $this->getPluginDefinition();
    return $plugin_definition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription(): string {
    $plugin_definition = $this->getPluginDefinition();
    return $plugin_definition['description'] ?? '';
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight(): int {
    if (isset($this->configuration['weight'])) {
      return $this->configuration['weight'];
    }
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight): void {
    $this->configuration['weight'] = $weight;
  }

}
