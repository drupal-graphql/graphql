<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\String;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Transforms a string to uppercase.
 */
#[DataProducer(
  id: "uppercase",
  name: new TranslatableMarkup("Uppercase"),
  description: new TranslatableMarkup("Transforms a string to uppercase."),
  produces: new ContextDefinition(
    data_type: "string",
    label: new TranslatableMarkup("Uppercase converted string")
  ),
  consumes: [
    "string" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("String")
    ),
  ]
)]
class Uppercase extends DataProducerPluginBase {

  /**
   * Value resolver.
   *
   * @param string $string
   *   The input string to convert to uppercase.
   *
   * @return string
   *   The input string converted to uppercase.
   */
  public function resolve(string $string): string {
    return strtoupper($string);
  }

}
