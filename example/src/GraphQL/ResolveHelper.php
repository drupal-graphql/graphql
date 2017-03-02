<?php

namespace Drupal\graphql_example\GraphQL;

use Drupal\graphql_example\GraphQL\Type\MenuType;
use Drupal\graphql_example\GraphQL\Type\PageType;
use Drupal\node\NodeInterface;
use Drupal\system\MenuInterface;

class ResolveHelper {

   /**
   * Resolves a given object into its corresponding type.
   *
   * @param $object
   *   The object to resolve the type of.
   *
   * @return \Youshido\GraphQL\Type\AbstractType|null
   *   The resolved type.
   */
  public static function resolveType($object) {
    if ($object instanceof MenuInterface) {
      return new MenuType();
    }

    if ($object instanceof NodeInterface) {
      return static::resolveNodeType($object);
    }

    return NULL;
  }

  /**
   * Resolves a given node object's type.
   *
   * @param \Drupal\node\NodeInterface $node
   *   The node to the resolve the type of.
   *
   * @return \Youshido\GraphQL\Type\AbstractType|null
   *   The resolved type.
   */
  protected static function resolveNodeType(NodeInterface $node) {
    if ($node->bundle() === 'page') {
      return new PageType();
    }

    return NULL;
  }
}
