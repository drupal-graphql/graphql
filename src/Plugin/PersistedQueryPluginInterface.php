<?php

namespace Drupal\graphql\Plugin;

use Drupal\Component\Plugin\ConfigurableInterface;
use Drupal\Component\Plugin\PluginInspectionInterface;
use GraphQL\Server\OperationParams;

/**
 * Defines plugins that represent persisted GraphQL queries.
 */
interface PersistedQueryPluginInterface extends ConfigurableInterface, PluginInspectionInterface {

  /**
   * Returns a query if this plugin has it.
   *
   * @param string $id
   *   ID of the persisted query.
   * @param \GraphQL\Server\OperationParams $operation
   *   The operation with parameters.
   *
   * @return string|null
   *   The actual GraphQL query, or NULL if this plugin does not support a query
   *   with that ID.
   */
  public function getQuery($id, OperationParams $operation);

  /**
   * Returns the label for use on the administration pages.
   *
   * @return string
   *   The administration label.
   */
  public function label();

  /**
   * Returns the plugin's description.
   *
   * @return string
   *   The plugin description.
   */
  public function getDescription();

  /**
   * Returns the weight of this plugin instance.
   *
   * @return int
   *   The default weight for the given stage.
   */
  public function getWeight();

  /**
   * Sets the weight for this plugin instance.
   *
   * @param int $weight
   *   The weight.
   */
  public function setWeight($weight);

}
