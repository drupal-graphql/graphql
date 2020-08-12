<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\String;

use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
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
   * @param $string
   *
   * @return string
   *   Returns all alphabetic characters in uppercase form.
   */
  public function resolve($string) {
    return strtoupper($string);
  }

}
