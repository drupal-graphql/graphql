<?php

namespace Drupal\graphql\Plugin\GraphQL\Traits;

use Drupal\Component\Plugin\PluginBase;

/**
 * Plugin compatibility trait.
 *
 * Trait to easily implement Drupal plugin interfaces without
 * extending PluginBase. Unfortunately a copy of PluginBase.
 *
 * TODO: Find DRYer solution.
 */
trait PluginTrait {

  /**
   * The plugin_id.
   *
   * @var string
   */
  protected $pluginId;

  /**
   * The plugin implementation definition.
   *
   * @var array
   */
  protected $pluginDefinition;

  /**
   * Configuration information passed into the plugin.
   *
   * When using an interface like
   * \Drupal\Component\Plugin\ConfigurablePluginInterface, this is where the
   * configuration should be stored.
   *
   * Plugin configuration is optional, so plugin implementations must provide
   * their own setters and getters.
   *
   * @var array
   */
  protected $configuration;

  /**
   * Constructs a Drupal\Component\Plugin\PluginBase object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $pluginId
   *   The plugin_id for the plugin instance.
   * @param array $pluginDefinition
   *   The plugin implementation definition.
   */
  public function constructPlugin(array $configuration, $pluginId, array $pluginDefinition) {
    $this->configuration = $configuration;
    $this->pluginId = $pluginId;
    $this->pluginDefinition = $pluginDefinition;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginId() {
    return $this->pluginId;
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseId() {
    $pluginId = $this->getPluginId();
    if (strpos($pluginId, PluginBase::DERIVATIVE_SEPARATOR)) {
      list($pluginId) = explode(PluginBase::DERIVATIVE_SEPARATOR, $pluginId, 2);
    }
    return $pluginId;
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeId() {
    $pluginId = $this->getPluginId();
    $derivativeId = NULL;
    if (strpos($pluginId, PluginBase::DERIVATIVE_SEPARATOR)) {
      list(, $derivativeId) = explode(PluginBase::DERIVATIVE_SEPARATOR, $pluginId, 2);
    }
    return $derivativeId;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginDefinition() {
    return $this->pluginDefinition;
  }

}
