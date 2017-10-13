<?php

namespace Drupal\graphql\Plugin\MenuLink\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaPluginManager;
use Symfony\Component\DependencyInjection\ContainerInterface;

class VoyagerMenuLinkDeriver extends DeriverBase implements ContainerDeriverInterface {

  /**
   * @var \Drupal\graphql\Plugin\GraphQL\SchemaPluginManager
   */
  protected $schemaManager;

  /**
   * Constructs a ExplorerMenuLinkDeriver object.
   *
   * @param \Drupal\graphql\Plugin\GraphQL\SchemaPluginManager $schemaManager
   *   The schema plugin manager service.
   */
  public function __construct(SchemaPluginManager $schemaManager) {
    $this->schemaManager = $schemaManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) {
    return new static($container->get('plugin.manager.graphql.schema'));
  }

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    foreach ($this->schemaManager->getDefinitions() as $key => $definition) {
      $this->derivatives[$key] = [
        'route_name' => "graphql.voyager.$key",
      ] + $basePluginDefinition;
    }

    return $this->derivatives;
  }

}
