<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Routing\Url;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Generates a URL path from an URL object.
 *
 * @todo Fix the type of the input context.
 */
#[DataProducer(
  id: "url_path",
  name: new TranslatableMarkup("Url path"),
  description: new TranslatableMarkup("The processed url path."),
  produces: new ContextDefinition(
    data_type: "string",
    label: new TranslatableMarkup("Path"),
  ),
  consumes: [
    "url" => new ContextDefinition(
      data_type: "any",
      label: new TranslatableMarkup("Url"),
    ),
  ],
)]
class UrlPath extends DataProducerPluginBase {

  /**
   * Resolver.
   */
  public function resolve(Url $url, RefinableCacheableDependencyInterface $metadata): string {
    $url = $url->toString(TRUE);
    $metadata->addCacheableDependency($url);

    return $url->getGeneratedUrl();
  }

}
