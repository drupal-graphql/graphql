<?php

namespace Drupal\graphql_address\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Drupal\address\AddressInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the address administrative area.
 *
 * @GraphQLField(
 *   id = "administrative_area",
 *   name = "administrative_area",
 *   type = "String",
 *   types = {"Address"}
 * )
 */
class AdministrativeArea extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof AddressInterface) {
      yield $value->get('administrative_area')->getValue();
    }
  }

}
