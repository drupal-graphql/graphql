<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Strings;

use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Transforms a string to uppercase.
 *
 * @DataProducer(
 *   id = "uppercase",
 *   name = @Translation("Uppercase"),
 *   description = @Translation("Transforms a string to uppercase."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Uppercased string")
 *   ),
 *   consumes = {
 *     "string" = @ContextDefinition("string",
 *       label = @Translation("String")
 *     )
 *   }
 * )
 */
class Uppercase extends DataProducerPluginBase {

  /**
   * Value resolver.
   *
   * @param string $string
   *   String input.
   *
   * @return string
   *   Upper-cased string.
   */
  public function resolve($string) {
    return strtoupper($string);
  }

}
