<?php

declare(strict_types=1);

namespace Drupal\graphql_dataproducers_test\Plugin\GraphQL\DataProducer;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Test data producer that returns whether two values in a tuple are equal.
 *
 * Used in CustomScalarTest.
 */
#[DataProducer(
  id: "test_compare_tuple",
  name: new TranslatableMarkup("Test compare tuple"),
  description: new TranslatableMarkup("Compares the values of a tuple."),
  produces: new ContextDefinition(
    data_type: "any",
    label: new TranslatableMarkup("Equal")
  ),
  consumes: [
    "input" => new ContextDefinition(
      data_type: "any",
      label: new TranslatableMarkup("Input tuple")
    ),
  ],
)]
class TestCompareTuple extends DataProducerPluginBase {

  /**
   * Checks whether two values in a tuple are equal.
   *
   * @param array $input
   *   The tuple.
   *
   * @return bool
   *   Whether they are equal.
   */
  public function resolve(array $input): bool {
    return $input[0] === $input[1];
  }

}
