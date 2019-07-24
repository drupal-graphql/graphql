<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Utility;

use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * @DataProducer(
 *   id = "seek",
 *   name = @Translation("Seek"),
 *   description = @Translation("Seeks an array position."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Element")
 *   ),
 *   consumes = {
 *     "input" = @ContextDefinition("any",
 *       label = @Translation("Input array"),
 *       required = FALSE
 *     ),
 *     "position" = @ContextDefinition("integer",
 *       label = @Translation("Seek position")
 *     )
 *   }
 * )
 */
class Seek extends DataProducerPluginBase {

  /**
   * @param array $input
   *  The input array.
   * @param int position
   *  The position to seek.
   * @return mixed
   *  The element at the specified position.
   */
  public function resolve(array $input, $position) {
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
