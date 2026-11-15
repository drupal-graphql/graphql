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
   * {@inheritdoc}
   */
  public function getConfiguration() {
    return $this->configuration;
  }

  /**
   * {@inheritdoc}
   */
  public function setConfiguration(array $configuration): void {
    $this->configuration = $configuration + $this->defaultConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [];
  }

  /**
   * {@inheritdoc}
   */
  public function label(): string {
    /** @var array{label: string|\Stringable, description?: string|\Stringable} $plugin_definition */
    $plugin_definition = $this->getPluginDefinition();
    return (string) $plugin_definition['label'];
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription(): string {
    /** @var array{label: string|\Stringable, description?: string|\Stringable} $plugin_definition */
    $plugin_definition = $this->getPluginDefinition();
    return (string) ($plugin_definition['description'] ?? '');
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight(): int {
    /** @var array{weight?: int} $configuration */
    $configuration = $this->configuration;
    return $configuration['weight'] ?? 0;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight): void {
    $this->configuration['weight'] = $weight;
  }

}
