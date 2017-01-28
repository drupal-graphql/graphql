<?php

namespace Drupal\graphql_example\GraphQL;

use Drupal\graphql_example\GraphQL\Type\MenuType;
use Drupal\system\MenuInterface;

class ResolveHelper {

   /**
   * Resolves a given object into its corresponding type.
   *
   * @param $object
   *   The object to resolve the type of.
   *
   * @return \Youshido\GraphQL\Type\AbstractType|null
   */
  public static function resolveType($object) {
    if ($object instanceof MenuInterface) {
      return new MenuType();
    }

    return NULL;
  }
}
