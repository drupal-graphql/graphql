<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Images;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Returns the URL of an image derivative.
 */
#[DataProducer(
  id: "image_style_url",
  name: new TranslatableMarkup("Image Style URL"),
  description: new TranslatableMarkup("Returns the URL of an image derivative."),
  produces: new ContextDefinition(
    data_type: "string",
    label: new TranslatableMarkup("URL")
  ),
  consumes: [
    "derivative" => new ContextDefinition(
      data_type: "any",
      label: new TranslatableMarkup("Derivative")
    ),
  ]
)]
class ImageResourceUrl extends DataProducerPluginBase {

  /**
   * Simply checks the url property in a given derivative result.
   */
  public function resolve(array $derivative): string {
    return $derivative['url'] ?? '';
  }

}
