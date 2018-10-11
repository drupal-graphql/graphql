<?php

namespace Drupal\Tests\graphql\Kernel\DataProducer;

use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Drupal\field\Tests\EntityReference\EntityReferenceTestTrait;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\system\Entity\Menu;
use Drupal\Tests\Core\Menu\MenuLinkMock;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\Core\Menu\MenuLinkTreeElement;

/**
 * Data producers Menu test class.
 *
 * @group graphql
 */
class MenuTest extends GraphQLTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->installEntitySchema('menu_link_content');

    $this->dataProducerManager = $this->container->get('plugin.manager.graphql.data_producer');
    $this->menuLinkManager = \Drupal::service('plugin.manager.menu.link');

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
      'link' => ['uri' => 'internal:/menu-test/hierarchy/parent'],
    ];
    $link = MenuLinkContent::create($parent);
    $link->save();
    $links['parent'] = $link->getPluginId();

    $child_1 = $base_options + [
      'link' => ['uri' => 'internal:/menu-test/hierarchy/parent/child'],
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
      'parent' => $links['parent'],
    ];
    $link = MenuLinkContent::create($child_2);
    $link->save();
    $links['child-2'] = $link->getPluginId();
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Menu\MenuLinks::resolve
   */
  public function testMenuLinks() {
    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'menu_links',
      'configuration' => []
    ]);
    $result = $plugin->resolve($this->menu);
    $count = 0;
    foreach ($result as $link_tree) {
      $this->assertInstanceOf(MenuLinkTreeElement::class, $link_tree);
      $count += $link_tree->count();
    }
    $this->assertEquals(5, $count);
  }
}
