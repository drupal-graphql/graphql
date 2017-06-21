<?php

namespace Drupal\graphql_address\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Drupal\address\AddressInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the address family name.
 *
 * @GraphQLField(
 *   id = "family_name",
 *   name = "family_name",
 *   type = "String",
 *   types = {"Address"}
 * )
 */
class FamilyName extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof AddressInterface) {
      yield $value->get('family_name')->getValue();
    }
  }

}
