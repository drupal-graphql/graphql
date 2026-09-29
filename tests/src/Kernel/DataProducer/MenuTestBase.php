<?php

declare(strict_types=1);

namespace Drupal\Tests\graphql\Kernel\DataProducer;

use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\system\Entity\Menu;

/**
 * Shared menu fixture for data producer tests.
 */
abstract class MenuTestBase extends GraphQLTestBase {

  /**
   * Test menu.
   */
  protected Menu $menu;

  /**
   * Test link tree array.
   *
   * @var array<\Drupal\Core\Menu\MenuLinkTreeElement>
   */
  protected array $linkTree;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('menu_link_content');

    $this->menu = Menu::create([
      'id' => 'menu_test',
      'label' => 'Test menu',
      'description' => 'Description text',
    ]);

    $this->menu->save();

    $base_options = [
      'title' => 'Menu link test',
      'provider' => 'graphql',
      'menu_name' => 'menu_test',
    ];

    $parent = $base_options + [
      'link' => [
        'uri' => 'internal:/menu-test/hierarchy/parent',
        'options' => [
          'attributes' => [
            'target' => '_blank',
          ],
        ],
      ],
      'description' => 'Test description',
    ];
    $link = MenuLinkContent::create($parent);
    $link->save();
    $links['parent'] = $link->getPluginId();

    $child_1 = $base_options + [
      'link' => ['uri' => 'internal:/menu-test/hierarchy/parent/child'],
      'description' => 'Child 1 description',
      'parent' => $links['parent'],
    ];
    $link = MenuLinkContent::create($child_1);
    $link->save();
    $links['child-1'] = $link->getPluginId();

    $child_1_1 = $base_options + [
      'link' => ['uri' => 'internal:/menu-test/hierarchy/parent/child2/child'],
      'parent' => $links['child-1'],
    ];
    $link = MenuLinkContent::create($child_1_1);
    $link->save();
    $links['child-1-1'] = $link->getPluginId();

    $child_1_2 = $base_options + [
      'link' => ['uri' => 'internal:/menu-test/hierarchy/parent/child2/child'],
      'parent' => $links['child-1'],
    ];
    $link = MenuLinkContent::create($child_1_2);
    $link->save();
    $links['child-1-2'] = $link->getPluginId();

    $child_2 = $base_options + [
      'link' => ['uri' => 'internal:/menu-test/hierarchy/parent/child'],
      'description' => NULL,
      'parent' => $links['parent'],
    ];
    $link = MenuLinkContent::create($child_2);
    $link->save();
    $links['child-2'] = $link->getPluginId();

    $menuLinkTree = $this->container->get('menu.link_tree');
    $this->linkTree = $menuLinkTree->load('menu_test', new MenuTreeParameters());
  }

}
