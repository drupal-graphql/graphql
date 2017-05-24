<?php

namespace Drupal\graphql_image\Plugin\GraphQL\Interfaces;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql_core\GraphQL\InterfacePluginBase;
use Drupal\graphql_core\GraphQLSchemaManagerInterface;
use Drupal\image\Plugin\Field\FieldType\ImageItem;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * ...
 *
 * @GraphQLInterface(
 *   id = "image_resource",
 *   name = "ImageResource",
 *   fields = {
 *     "image_url",
 *     "image_height",
 *     "image_width"
 *   }
 * )
 */
class ImageResource extends InterfacePluginBase implements ContainerFactoryPluginInterface {

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
  public function __construct(array $configuration, $pluginId, $pluginDefinition, GraphQLSchemaManagerInterface $schemaManager) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->schemaManager = $schemaManager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('graphql_core.schema_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function resolveType($object) {
    if ($object instanceof ImageItem) {
      return $this->schemaManager->findByName('Image', [GRAPHQL_CORE_TYPE_PLUGIN]);
    }
    if (is_array($object)) {
      return $this->schemaManager->findByName('ImageDerivative', [GRAPHQL_CORE_TYPE_PLUGIN]);
    }
  }

}
