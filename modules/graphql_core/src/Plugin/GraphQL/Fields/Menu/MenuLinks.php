<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Menu;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Drupal\system\MenuInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieves a menus links.
 *
 * @GraphQLField(
 *   id = "menu_links",
 *   secure = true,
 *   name = "links",
 *   type = "MenuLink",
 *   parents = {"Menu"},
 *   multi = true
 * )
 */
class MenuLinks extends FieldPluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

  /**
   * The menu link tree.
   *
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuLinkTree;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static($configuration, $pluginId, $pluginDefinition, $container->get('menu.link_tree'));
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, MenuLinkTreeInterface $menuLinkTree) {
    $this->menuLinkTree = $menuLinkTree;
    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof MenuInterface) {
      $tree = $this->menuLinkTree->load($value->id(), new MenuTreeParameters());

      $manipulators = [
        ['callable' => 'menu.default_tree_manipulators:checkAccess'],
        ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
      ];

      foreach (array_filter($this->menuLinkTree->transform($tree, $manipulators), function (MenuLinkTreeElement $item) {
        return $item->link instanceof MenuLinkInterface && $item->link->isEnabled();
      }) as $branch) {
        yield $branch;
      }
    }
  }

}
