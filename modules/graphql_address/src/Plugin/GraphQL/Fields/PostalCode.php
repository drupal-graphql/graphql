<?php

namespace Drupal\graphql_address\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Drupal\address\AddressInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the address postal code.
 *
 * @GraphQLField(
 *   id = "postal_code",
 *   name = "postal_code",
 *   type = "String",
 *   types = {"Address"}
 * )
 */
class PostalCode extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof AddressInterface) {
      yield $value->get('postal_code')->getValue();
    }
  }

}
