<?php

declare(strict_types=1);

namespace Drupal\graphql_dataproducers_test\Plugin\GraphQL\DataProducer;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Tests\graphql\TestInvocationCounter;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;

/**
 * Test data producer that returns user ID and invokes a counter.
 *
 * Used in CurrentUserTest.
 */
#[DataProducer(
  id: "test_user_id_counting",
  name: new TranslatableMarkup("Test user ID counting"),
  description: new TranslatableMarkup("Returns the user ID and invokes a counter for each resolution."),
  produces: new ContextDefinition(
    data_type: "integer",
    label: new TranslatableMarkup("User ID")
  ),
  consumes: [
    "user" => new ContextDefinition(
      data_type: "any",
      label: new TranslatableMarkup("User account")
    ),
    "counter" => new ContextDefinition(
      data_type: "any",
      label: new TranslatableMarkup("TestInvocationCounter")
    ),
  ],
)]
class TestUserIdCounting extends DataProducerPluginBase {

  /**
   * Invokes the counter and returns the user ID.
   *
   * @param \Drupal\Core\Session\AccountInterface $user
   *   The user account.
   * @param \Drupal\Tests\graphql\TestInvocationCounter $counter
   *   Counter invoked for side effect.
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $field
   *   The field context.
   *
   * @return int
   *   The user ID.
   */
  public function resolve(AccountInterface $user, TestInvocationCounter $counter, FieldContext $field): int {
    $counter->increment();
    return (int) $user->id();
  }

}
