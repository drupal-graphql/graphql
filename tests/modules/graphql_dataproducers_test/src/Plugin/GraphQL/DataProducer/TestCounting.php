<?php

declare(strict_types=1);

namespace Drupal\graphql_dataproducers_test\Plugin\GraphQL\DataProducer;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Tests\graphql\TestInvocationCounter;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Test data producer that returns a value and invokes a counter.
 *
 * Used in ResultCacheTest.
 */
#[DataProducer(
  id: "test_counting",
  name: new TranslatableMarkup("Test counting"),
  description: new TranslatableMarkup("Returns a value and invokes a counter for each resolution."),
  produces: new ContextDefinition(
    data_type: "any",
    label: new TranslatableMarkup("Return value")
  ),
  consumes: [
    "return_value" => new ContextDefinition(
      data_type: "any",
      label: new TranslatableMarkup("Value to return")
    ),
    "counter" => new ContextDefinition(
      data_type: "any",
      label: new TranslatableMarkup("TestInvocationCounter")
    ),
  ],
)]
class TestCounting extends DataProducerPluginBase {

  /**
   * Invokes the counter and returns the value.
   *
   * @param T $return_value
   *   The value to return.
   * @param \Drupal\Tests\graphql\TestInvocationCounter $counter
   *   Counter invoked for side effect.
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $field
   *   The field context.
   *
   * @return T
   *   The return value.
   *
   * @template T
   */
  public function resolve(mixed $return_value, TestInvocationCounter $counter, FieldContext $field): mixed {
    $counter->increment();
    return $return_value;
  }

}
