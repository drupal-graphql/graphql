<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Images;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Drupal\image\Plugin\Field\FieldType\ImageItem;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Execution\ResolveInfo;
use Drupal\image\Entity\ImageStyle;

/**
 * Retrieve the image field derivative (image style).
 *
 * @GraphQLField(
 *   id = "image_derivative",
 *   secure = true,
 *   name = "derivative",
 *   type = "ImageResource",
 *   nullable = true,
 *   arguments = {
 *     "style" = "ImageStyleId"
 *   },
 *   field_types = {
 *     "image"
 *   },
 *   provider = "image",
 *   deriver = "Drupal\graphql_core\Plugin\Deriver\Fields\EntityFieldPropertyDeriver"
 * )
 */
class ImageDerivative extends FieldPluginBase implements ContainerFactoryPluginInterface {

  use DependencySerializationTrait;

  /**
   * The image factory.
   *
   * @var \Drupal\Core\Image\ImageFactory
   */
  protected $imageFactory;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, ImageFactory $imageFactory) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->imageFactory = $imageFactory;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static($configuration, $pluginId, $pluginDefinition, $container->get('image.factory'));
  }

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof ImageItem && $value->entity->access('view') && $style = ImageStyle::load($args['style'])) {
      $file = $value->entity;

      // Determine the dimensions of the styled image.
      $dimensions = [
        'width' => $value->width,
        'height' => $value->height,
      ];

      $style->transformDimensions($dimensions, $file->getFileUri());

      yield [
        'url' => $style->buildUrl($file->getFileUri()),
        'width' => $dimensions['width'],
        'height' => $dimensions['height'],
      ];
    }
  }

}
