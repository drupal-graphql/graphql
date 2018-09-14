<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Routing\Url;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Url;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * TODO: Fix the type of the input context.
 *
 * @DataProducer(
 *   id = "url_path",
 *   name = @Translation("Url path"),
 *   description = @Translation("The processed url path."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Path")
 *   ),
 *   consumes = {
 *     "url" = @ContextDefinition("any",
 *       label = @Translation("Url")
 *     )
 *   }
 * )
 */
class UrlPath extends DataProducerPluginBase {

  /**
   * @param \Drupal\Core\Url $url
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   *
   * @return string
   */
  public function resolve(Url $url, RefinableCacheableDependencyInterface $metadata) {
    $url = $url->toString(TRUE);
    $metadata->addCacheableDependency($url);

    return $url->getGeneratedUrl();
  }

}
