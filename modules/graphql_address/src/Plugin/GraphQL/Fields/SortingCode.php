<?php

namespace Drupal\graphql_address\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Drupal\address\AddressInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the address sorting code.
 *
 * @GraphQLField(
 *   id = "sorting_code",
 *   name = "sorting_code",
 *   type = "String",
 *   types = {"Address"}
 * )
 */
class SortingCode extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof AddressInterface) {
      yield $value->get('sorting_code')->getValue();
    }
  }

}
