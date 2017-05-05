<?php

namespace Drupal\graphql_image\Plugin\GraphQL\Fields;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Drupal\image\Plugin\Field\FieldType\ImageItem;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Execution\ResolveInfo;
use Drupal\image\Entity\ImageStyle;

/**
 * Retrieve the image field derivative (image style).
 *
 * @GraphQLField(
 *   id = "image_derivative",
 *   name = "derivative",
 *   type = "ImageStyle",
 *   nullable = true,
 *   arguments = {
 *     "style" = "ImageStyleId"
 *   },
 *   types = {
 *     "Image"
 *   }
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
    if ($value instanceof ImageItem && $style = ImageStyle::load($args['style'])) {
      $file = $value->entity;
      $styleUri = $style->buildUri($file->getFileUri());
      if (!file_exists($styleUri)) {
        $style->createDerivative($file->getFileUri(), $styleUri);
      }
      $derivative = $this->imageFactory->get($styleUri);

      // Return null if derivative generation didn't succeed.
      if (isset($derivative) && $derivative->isValid()) {
        yield [
          'url' => $style ? $style->buildUrl($file->getFileUri()) : Url::fromUri(file_create_url($file->getFileUri()))->toString(),
          'mimeType' => $derivative->getMimeType(),
          'width' => $derivative->getWidth(),
          'height' => $derivative->getHeight(),
          'fileSize' => $derivative->getFileSize(),
        ];
      }
    }
  }

}
