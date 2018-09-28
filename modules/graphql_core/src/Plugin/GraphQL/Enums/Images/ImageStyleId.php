<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Enums\Images;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Plugin\GraphQL\Enums\EnumPluginBase;
use Drupal\graphql\Utility\StringHelper;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @GraphQLEnum(
 *   id = "image_style_id",
 *   name = "ImageStyleId",
 *   provider = "image"
 * )
 */
class ImageStyleId extends EnumPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * ImageStyleId constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param array $pluginDefinition
   *   The plugin definition array.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, EntityTypeManagerInterface $entityTypeManager) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * {@inheritdoc}
   */
  public function buildEnumValues($definition) {
    $items = [];

    $storage = $this->entityTypeManager->getStorage('image_style');
    foreach ($storage->loadMultiple() as $imageStyle) {
      $items[StringHelper::upperCase($imageStyle->id())] = [
        'value' => $imageStyle->id(),
        'description' => $imageStyle->label(),
      ];
    }

    return $items;
  }

}
