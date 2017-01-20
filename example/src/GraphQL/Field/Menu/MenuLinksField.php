<?php

namespace Drupal\graphql_example\GraphQL\Field\Menu;

use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\graphql_example\GraphQL\Field\EntityArrayConnectionFieldTrait;
use Drupal\graphql_example\GraphQL\Field\SelfAwareField;
use Drupal\graphql_example\GraphQL\Type\MenuTreeItemType;
use Drupal\system\MenuInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Youshido\GraphQL\Config\Field\FieldConfig;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Relay\Connection\ArrayConnection;
use Youshido\GraphQL\Relay\Connection\Connection;

class MenuLinksField extends SelfAwareField implements ContainerAwareInterface {
  use ContainerAwareTrait;
  use EntityArrayConnectionFieldTrait;

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
    if ($value instanceof MenuInterface) {
      /** @var \Drupal\Core\Menu\MenuLinkTreeInterface $treeStorage */
      $menuTree = $this->container->get('menu.link_tree');

      // Load the menu tree with all items.
      $parameters = new MenuTreeParameters();
      $tree = $menuTree->load($value->id(), $parameters);

      $manipulators = [
        ['callable' => 'menu.default_tree_manipulators:checkAccess'],
        ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
      ];

      $tree = array_filter($menuTree->transform($tree, $manipulators), function (MenuLinkTreeElement $item) {
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
    return 'links';
  }

  /**
   * {@inheritdoc}
   */
  public function getType() {
    return Connection::connectionDefinition(new MenuTreeItemType());
  }
}
