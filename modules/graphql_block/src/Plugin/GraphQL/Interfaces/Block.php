<?php

namespace Drupal\graphql_block\Plugin\GraphQL\Interfaces;

use Drupal\block_content\Entity\BlockContent;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql_core\GraphQL\InterfacePluginBase;
use Drupal\graphql_core\GraphQLSchemaManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Root interface for Drupal blocks exposed to GraphQL.
 *
 * @GraphQLInterface(
 *   name = "Block"
 * )
 */
class Block extends InterfacePluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

  /**
   * The schema manager.
   *
   * @var \Drupal\graphql_core\GraphQLSchemaManagerInterface
   */
  protected $schemaManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, GraphQLSchemaManagerInterface $schemaManager) {
    $this->schemaManager = $schemaManager;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('graphql.schema_manager'));
  }

  /**
   * {@inheritdoc}
   */
  public function resolveType($object) {
    if ($object instanceof BlockContent) {
      return $this->schemaManager
        ->findByName(graphql_core_camelcase([$object->getEntityTypeId(), $object->bundle()]), [GRAPHQL_CORE_TYPE_PLUGIN]);
    }
    else {
      // TODO: Detect custom block plugins?
      return $this->schemaManager->findByName('BlockConfig', [GRAPHQL_CORE_TYPE_PLUGIN]);
    }
  }

}
