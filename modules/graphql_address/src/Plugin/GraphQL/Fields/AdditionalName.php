<?php

namespace Drupal\graphql_address\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Drupal\address\AddressInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the address additional name.
 *
 * @GraphQLField(
 *   id = "additional_name",
 *   name = "additional_name",
 *   type = "String",
 *   types = {"Address"}
 * )
 */
class AdditionalName extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof AddressInterface) {
      yield $value->get('additional_name')->getValue();
    }
  }

}
