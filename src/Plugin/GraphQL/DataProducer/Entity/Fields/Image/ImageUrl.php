<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\Fields\Image;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\file\FileInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * @DataProducer(
 *   id = "image_url",
 *   name = @Translation("Image URL"),
 *   description = @Translation("Returns the url of an image entity."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("URL")
 *   ),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity")
 *     )
 *   }
 * )
 */
class ImageUrl extends DataProducerPluginBase {

  /**
   * @param \Drupal\file\FileInterface $entity
   *
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   *
   * @return mixed
   */
  public function resolve(FileInterface $entity, RefinableCacheableDependencyInterface $metadata) {
    $access = $entity->access('view', NULL, TRUE);
    $metadata->addCacheableDependency($access);
    if ($access->isAllowed()) {
      return file_create_url($entity->getFileUri());
    }
  }

}
