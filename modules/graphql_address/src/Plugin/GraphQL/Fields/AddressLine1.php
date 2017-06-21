<?php

namespace Drupal\graphql_address\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Drupal\address\AddressInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the address line 1.
 *
 * @GraphQLField(
 *   id = "address_line1",
 *   name = "address_line1",
 *   type = "String",
 *   types = {"Address"}
 * )
 */
class AddressLine1 extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof AddressInterface) {
      yield $value->get('address_line1')->getValue();
    }
  }

}
