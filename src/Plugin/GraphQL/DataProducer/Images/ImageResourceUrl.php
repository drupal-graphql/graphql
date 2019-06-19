<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Images;

use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * @DataProducer(
 *   id = "image_style_url",
 *   name = @Translation("Image Style URL"),
 *   description = @Translation("Returns the URL of an image derivative."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("URL")
 *   ),
 *   consumes = {
 *     "derivative" = @ContextDefinition("any",
 *       label = @Translation("Derivative")
 *     )
 *   }
 * )
 */
class ImageResourceUrl extends DataProducerPluginBase {

  /**
   * @param $derivative
   *
   * @return mixed
   */
  public function resolve($derivative) {
    return $derivative['url'] ?? '';
  }

}
