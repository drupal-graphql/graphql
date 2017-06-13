<?php

namespace Drupal\graphql_address\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Drupal\address\AddressInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the address country code.
 *
 * @GraphQLField(
 *   id = "country_code",
 *   name = "country_code",
 *   type = "String",
 *   types = {"Address"}
 * )
 */
class Country extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof AddressInterface) {
      yield $value->get('country_code')->getValue();
    }
  }

}
