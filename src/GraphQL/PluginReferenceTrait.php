<?php

namespace Drupal\graphql\GraphQL;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface;

trait PluginReferenceTrait {

  /**
   * The associated type system plugin.
   *
   * @var \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface
   */
  protected $plugin;

  /**
   * The schema builder object.
   *
   * @var \Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface
   */
  protected $builder;

  /**
   * {@inheritdoc}
   */
  public function getPlugin() {
    if (is_array($this->plugin)) {
      $this->plugin = $this->builder->getInstance(
        $this->plugin['type'],
        $this->plugin['id'],
        $this->plugin['configuration']
      );
    }

    return $this->plugin;
  }

  /**
   * {@inheritdoc}
   */
  public function __sleep() {
    // Instead of serializing the referenced plugin, we just serialize the
    // plugin id and configuration.
    if ($this->plugin instanceof TypeSystemPluginInterface) {
      $this->plugin = [
        'id' => $this->plugin->getPluginId(),
        'type' => $this->plugin->getPluginDefinition()['pluginType'],
        'configuration' => $this->plugin instanceof ConfigurablePluginInterface ? $this->plugin->getConfiguration() : [],
      ];
    }

    return array_keys(get_object_vars($this));
  }

}