<?php

namespace Drupal\graphql\Plugin\GraphQL;

use Drupal\Component\Plugin\ConfigurablePluginInterface;
use Drupal\graphql\GraphQL\Schema\Schema;
use Youshido\GraphQL\Execution\ResolveInfo;

trait TypeSystemPluginReferenceTrait {

  /**
   * The associated type system plugin.
   *
   * @var \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface
   */
  protected $plugin;

  /**
   * Retrieves the corresponding plugin instance from the resolve info.
   *
   * @param \Youshido\GraphQL\Execution\ResolveInfo $info
   *   The resolve info object.
   *
   * @return \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface|null
   *   The corresponding plugin instance for this edge.
   */
  protected function getPluginFromResolveInfo(ResolveInfo $info) {
    if (is_array($this->plugin)) {
      $schema = isset($info) ? $info->getExecutionContext()->getSchema() : NULL;
      if (!$schema instanceof Schema) {
        return NULL;
      }

      $schemaPlugin = $schema->getSchemaPlugin();
      if (!$schemaPlugin instanceof PluggableSchemaPluginInterface) {
        return NULL;
      }

      $typePlugin = $this->getPlugin($schemaPlugin->getSchemaBuilder());
      if (!$typePlugin instanceof TypeSystemPluginInterface) {
        return NULL;
      }

      return $typePlugin;
    }

    if ($this->plugin instanceof TypeSystemPluginInterface) {
      return $this->plugin;
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getPlugin(PluggableSchemaBuilderInterface $schemaBuilder) {
    if (is_array($this->plugin)) {
      $this->plugin = $schemaBuilder->getInstance(
        $this->plugin['type'],
        $this->plugin['id'],
        $this->plugin['configuration']
      );
    }

    if ($this->plugin instanceof TypeSystemPluginInterface) {
      return $this->plugin;
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function __sleep() {
    // Instead of serializing the referenced plugin, we just serialize the
    // plugin id and configuration.
    $this->plugin = [
      'id' => $this->plugin->getPluginId(),
      'type' => $this->plugin->getPluginDefinition()['pluginType'],
      'configuration' => $this->plugin instanceof ConfigurablePluginInterface ? $this->plugin->getConfiguration() : [],
    ];

    return array_keys(get_object_vars($this));
  }

  /**
   * {@inheritdoc}
   */
  public function isValidValue($value) {
    // TODO: Don't rely on a pre-initialized plugin.
    if ($this->plugin instanceof TypeValidationInterface) {
      return $this->plugin->isValidValue($value);
    }
    return parent::isValidValue($value);
  }

}