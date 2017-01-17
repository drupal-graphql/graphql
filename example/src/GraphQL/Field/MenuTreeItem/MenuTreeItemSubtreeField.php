<?php

namespace Drupal\graphql_example\GraphQL\Field\MenuTreeItem;

use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\graphql_example\GraphQL\Field\SelfAwareField;
use Drupal\graphql_example\GraphQL\Type\MenuTreeItemType;
use Youshido\GraphQL\Config\Field\FieldConfig;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Relay\Connection\ArrayConnection;
use Youshido\GraphQL\Relay\Connection\Connection;

class MenuTreeItemSubtreeField extends SelfAwareField {

  /**
   * {@inheritdoc}
   */
  public function build(FieldConfig $config) {
    $config->addArguments(Connection::connectionArgs());
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    if ($value instanceof MenuLinkTreeElement) {
      $tree = array_filter($value->subtree, function (MenuLinkTreeElement $item) {
        if ($item->link instanceof MenuLinkInterface) {
          return $item->link->isEnabled();
        }

        return TRUE;
      });

      return ArrayConnection::connectionFromArray($tree, $args);
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return 'subtree';
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return Connection::connectionDefinition(new MenuTreeItemType());
  }
}