<?php

declare(strict_types=1);

namespace Drupal\graphql_dataproducers_test\Plugin\GraphQL\DataProducer;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use GraphQL\Deferred;

/**
 * Test data producer that returns a Deferred resolving to a given value.
 *
 * Used in ResolverBuilderTest::testDeferredDefaultValue().
 */
#[DataProducer(
  id: "test_deferred",
  name: new TranslatableMarkup("Test deferred"),
  description: new TranslatableMarkup("Returns a Deferred that resolves to the given value."),
  produces: new ContextDefinition(
    data_type: "any",
    label: new TranslatableMarkup("Value")
  ),
  consumes: [
    "value" => new ContextDefinition(
      data_type: "any",
      label: new TranslatableMarkup("Value")
    ),
  ],
)]
class TestDeferred extends DataProducerPluginBase {

  /**
   * Resolves to a Deferred that returns the given value.
   *
   * @param T $value
   *   The value the Deferred will resolve to.
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $field
   *   The field context.
   *
   * @return \GraphQL\Deferred
   *   A deferred that resolves to $value.
   *
   * @template T
   */
  public function resolve(mixed $value, FieldContext $field): Deferred {
    return new Deferred(function () use ($value) {
      return $value;
    });
  }

}
