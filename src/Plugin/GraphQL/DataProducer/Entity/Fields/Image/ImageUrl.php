<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\Fields\Image;

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
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *
   * @return mixed
   */
  public function resolve(FileInterface $entity) {
    if ($entity->access('view')) {
      return file_create_url($entity->getFileUri());
    }
  }

}
