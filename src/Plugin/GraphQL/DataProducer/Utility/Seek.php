<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Utility;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Return an item from a list at a specified position.
 */
#[DataProducer(
  id: "seek",
  name: new TranslatableMarkup("Seek"),
  description: new TranslatableMarkup("Seeks an array position."),
  produces: new ContextDefinition(
    data_type: "any",
    label: new TranslatableMarkup("Element")
  ),
  consumes: [
    "input" => new ContextDefinition(
      data_type: "any",
      label: new TranslatableMarkup("Input array"),
      required: FALSE
    ),
    "position" => new ContextDefinition(
      data_type: "integer",
      label: new TranslatableMarkup("Seek position")
    ),
  ]
)]
class Seek extends DataProducerPluginBase {

  /**
   * Resolver.
   *
   * @param array $input
   *   The input array.
   * @param int $position
   *   The position to seek.
   *
   * @return mixed
   *   The element at the specified position.
   */
  public function resolve(array $input, int $position): mixed {
    $array_object = new \ArrayObject($input);
    $iterator = $array_object->getIterator();
    try {
      $iterator->seek($position);
    }
    catch (\OutOfBoundsException $e) {
      return NULL;
    }
    return $iterator->current();
  }

}
