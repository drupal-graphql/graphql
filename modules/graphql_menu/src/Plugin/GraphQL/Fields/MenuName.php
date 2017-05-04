<?php

namespace Drupal\graphql_menu\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Drupal\system\MenuInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve a menu's name.
 *
 * @GraphQLField(
 *   id = "menu_name",
 *   name = "name",
 *   type = "String",
 *   types = {"Menu"}
 * )
 */
class MenuName extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof MenuInterface) {
      yield $value->label();
    }
  }

}
