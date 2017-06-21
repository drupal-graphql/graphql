<?php

namespace Drupal\graphql_address\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Drupal\address\AddressInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the address line 2.
 *
 * @GraphQLField(
 *   id = "address_line2",
 *   name = "address_line2",
 *   type = "String",
 *   types = {"Address"}
 * )
 */
class AddressLine2 extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof AddressInterface) {
      yield $value->get('address_line2')->getValue();
    }
  }

}
