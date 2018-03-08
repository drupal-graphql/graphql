<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Menu;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Drupal\system\MenuInterface;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Retrieve a menu's name.
 *
 * @GraphQLField(
 *   id = "menu_name",
 *   secure = true,
 *   name = "name",
 *   description = @Translation("The menu's name."),
 *   type = "String",
 *   parents = {"Menu"}
 * )
 */
class MenuName extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof MenuInterface) {
      yield $value->label();
    }
  }

}
