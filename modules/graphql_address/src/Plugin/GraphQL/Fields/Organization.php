<?php

namespace Drupal\graphql_address\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Drupal\address\AddressInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the address organization.
 *
 * @GraphQLField(
 *   id = "organization",
 *   name = "organization",
 *   type = "String",
 *   types = {"Address"}
 * )
 */
class Organization extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof AddressInterface) {
      yield $value->get('organization')->getValue();
    }
  }

}
