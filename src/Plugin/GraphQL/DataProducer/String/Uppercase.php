<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\String;

use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Transforms a string to uppercase.
 *
 * @DataProducer(
 *   id = "uppercase",
 *   name = @Translation("Uppercase"),
 *   description = @Translation("Transforms a string to uppercase."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Uppercase converted string")
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
   *   The input string to convert to uppercase.
   *
   * @return string
   *   The input string converted to uppercase.
   */
  public function resolve(string $string): string {
    return strtoupper($string);
  }

}
