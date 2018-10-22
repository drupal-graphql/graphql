<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\Fields\Image;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\file\FileInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\image\Entity\ImageStyle;

/**
 * @DataProducer(
 *   id = "image_derivative",
 *   name = @Translation("Image Derivative"),
 *   description = @Translation("Returns an image derivative."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Image derivative properties")
 *   ),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity")
 *     ),
 *     "style" = @ContextDefinition("string",
 *       label = @Translation("Image style")
 *     )
 *   }
 * )
 */
class ImageDerivative extends DataProducerPluginBase {

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return mixed
   */
  public function resolve(FileInterface $entity, $style, RefinableCacheableDependencyInterface $metadata) {
    if ($entity->access('view') && $style = ImageStyle::load($style)) {
      // Determine the dimensions of the styled image.
      $dimensions = [
        'width' => $entity->width,
        'height' => $entity->height,
      ];

      $style->transformDimensions($dimensions, $entity->getFileUri());
      $metadata->addCacheableDependency($style);

      return [
        'url' => $style->buildUrl($entity->getFileUri()),
        'width' => $dimensions['width'],
        'height' => $dimensions['height'],
      ];
    }
  }

}
