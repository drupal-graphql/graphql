<?php

namespace Drupal\graphql_menu\Plugin\GraphQL\Types;

use Drupal\graphql_core\GraphQL\TypePluginBase;

/**
 * GraphQL type representing Drupal menu links.
 *
 * @GraphQLType(
 *   id = "menu_link",
 *   name = "MenuLink"
 * )
 */
class MenuLink extends TypePluginBase {
}
