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
 *       label = @Translation("Entity"),
 *       required = FALSE
 *     ),
 *     "style" = @ContextDefinition("string",
 *       label = @Translation("Image style")
 *     )
 *   }
 * )
 */
class ImageDerivative extends DataProducerPluginBase {

  /**
   * @param \Drupal\file\FileInterface $entity
   *
   * @param $style
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   *
   * @return mixed
   */
  public function resolve(FileInterface $entity = NULL, $style, RefinableCacheableDependencyInterface $metadata) {
    // Return if we dont have an entity.
    if (!$entity) {
      return NULL;
    }

    $access = $entity->access('view', NULL, TRUE);
    $metadata->addCacheableDependency($access);
    if ($access->isAllowed() && $image_style = ImageStyle::load($style)) {
      // Determine the dimensions of the styled image.
      $dimensions = [
        'width' => $entity->width,
        'height' => $entity->height,
      ];

      $image_style->transformDimensions($dimensions, $entity->getFileUri());
      $metadata->addCacheableDependency($image_style);

      return [
        'url' => $image_style->buildUrl($entity->getFileUri()),
        'width' => $dimensions['width'],
        'height' => $dimensions['height'],
      ];
    }

    return NULL;
  }

}
