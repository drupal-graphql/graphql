<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Menu;

use Drupal\Core\Menu\MenuLinkTreeElement;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\Context\EntityContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\system\MenuInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Return the menu links of a menu.
 *
 * @todo Fix output context type.
 */
#[DataProducer(
  id: "menu_links",
  name: new TranslatableMarkup("Menu links"),
  description: new TranslatableMarkup("Returns the menu links of a menu."),
  produces: new ContextDefinition(
    data_type: "any",
    label: new TranslatableMarkup("Menu link"),
    multiple: TRUE,
  ),
  consumes: [
    "menu" => new EntityContextDefinition(
      data_type: "entity:menu",
      label: new TranslatableMarkup("Menu"),
    ),
  ],
)]
class MenuLinks extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('menu.link_tree')
    );
  }

  /**
   * MenuItems constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param array $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Menu\MenuLinkTreeInterface $menuLinkTree
   *   The menu link tree service.
   */
  public function __construct(
    array $configuration,
    string $plugin_id,
    array $plugin_definition,
    protected MenuLinkTreeInterface $menuLinkTree,
  ) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * Resolver.
   *
   * @param \Drupal\system\MenuInterface $menu
   *   The menu to load links for.
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $context
   *   The context to add caching information to.
   *
   * @return array<\Drupal\Core\Menu\MenuLinkTreeElement>
   *   The list of menu links that are enabled and accessible.
   */
  public function resolve(MenuInterface $menu, FieldContext $context): array {
    // Ensure the cache is invalidated when the menu changes.
    $context->addCacheableDependency($menu);
    $id = $menu->id();
    if ($id === NULL) {
      return [];
    }
    $tree = $this->menuLinkTree->load($id, new MenuTreeParameters());

    $manipulators = [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ];

    return array_filter($this->menuLinkTree->transform($tree, $manipulators), static function (MenuLinkTreeElement $item) use ($context) {
      $context->addCacheableDependency($item->access);
      return $item->link->isEnabled() && $item->access !== NULL && $item->access->isAllowed();
    });
  }

}
