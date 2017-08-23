<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Interfaces;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql_core\GraphQL\InterfacePluginBase;
use Drupal\graphql_core\GraphQLSchemaManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Url as DrupalUrl;

/**
 * GraphQL interface for Urls.
 *
 * @GraphQLInterface(
 *   id = "url",
 *   name = "Url"
 * )
 */
class Url extends InterfacePluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

  /**
   * The GraphQL schema manager.
   *
   * @var \Drupal\graphql_core\GraphQLSchemaManagerInterface
   */
  protected $schemaManager;

  /**
   * {@inheritdoc}
   */
  public function resolveType($object) {
    if ($object instanceof DrupalUrl) {
      return $this->schemaManager->findByName($object->isExternal() ? 'ExternalUrl' : 'InternalUrl', [
        GRAPHQL_CORE_TYPE_PLUGIN,
      ]);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    GraphQLSchemaManagerInterface $schemaManager
  ) {
    $this->schemaManager = $schemaManager;
    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('graphql_core.schema_manager')
    );
  }


}
