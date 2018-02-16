<?php

namespace Drupal\graphql_plugin_test\Plugin\GraphQL\Types;

use Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * A bike type.
 *
 * @GraphQLType(
 *   id = "bike",
 *   name = "Bike",
 *   interfaces = {"Vehicle"}
 * )
 */
class Bike extends TypePluginBase {

  /**
   * {@inheritdoc}
   */
  public function applies($object, $context, ResolveInfo $info) {
    return $value['type'] == 'Bike';
  }

}
