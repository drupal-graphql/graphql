<?php

namespace Drupal\graphql_address\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Drupal\address\AddressInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the address given name.
 *
 * @GraphQLField(
 *   id = "given_name",
 *   name = "given_name",
 *   type = "String",
 *   types = {"Address"}
 * )
 */
class GivenName extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof AddressInterface) {
      yield $value->get('given_name')->getValue();
    }
  }

}
