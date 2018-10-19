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
use Drupal\Core\Menu\MenuTreeParameters;
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
    $this->menuLinkManager = $this->container->get('plugin.manager.menu.link');

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
          ]
        ],
      ],
      'description' => 'Test description'
    ];
    $link = MenuLinkContent::create($parent);
    $link->save();
    $links['parent'] = $link->getPluginId();
    $this->testLink = $link;

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

    $this->menuLinkTree = $this->container->get('menu.link_tree');
    $this->linkTree = $this->menuLinkTree->load('menu_test', new MenuTreeParameters());
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

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Menu\MenuTree\MenuTreeLink::resolve
   */
  public function testMenuTreeLink() {
    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'menu_tree_link',
      'configuration' => []
    ]);

    foreach ($this->linkTree as $link_tree) {
      $link = $plugin->resolve($link_tree);
      $this->assertEquals($link, $link_tree->link);
    }
  }


  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Menu\MenuTree\MenuTreeSubtree::resolve
   */
  public function testMenuTreeSubtree() {
    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'menu_tree_subtree',
      'configuration' => []
    ]);

    foreach ($this->linkTree as $link_tree) {
      $subtree = $plugin->resolve($link_tree);
      if (!empty($link_tree->subtree)) {
        $this->assertEquals($link_tree->subtree, $subtree);
      }
    }
  }

  /**
   * @covers Drupal\graphql\Plugin\GraphQL\DataProducer\Menu\MenuLink\MenuLinkAttribute::resolve
   */
  public function testMenuLinkAttribute() {
    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'menu_link_attribute',
      'configuration' => []
    ]);

    $attribute = 'target';

    foreach ($this->linkTree as $link_tree) {
      $options = $link_tree->link->getOptions();
      $link = $link_tree->link;
      if (!empty($options['attributes'][$attribute])) {
        $result = $plugin->resolve($link, $attribute);
        $this->assertEquals($options['attributes'][$attribute], $result);
      }
    }
  }

  /**
   * @covers Drupal\graphql\Plugin\GraphQL\DataProducer\Menu\MenuLink\MenuLinkDescription::resolve
   */
  public function testMenuLinkDescription() {
    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'menu_link_description',
      'configuration' => []
    ]);
    foreach ($this->linkTree as $link_tree) {
      $link = $link_tree->link;
      $this->assertEquals($link->getDescription(), $plugin->resolve($link));
    }
  }

  /**
   * @covers Drupal\graphql\Plugin\GraphQL\DataProducer\Menu\MenuLink\MenuLinkExpanded::resolve
   */
  public function testMenuLinkExpanded() {
    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'menu_link_expanded',
      'configuration' => []
    ]);
    foreach ($this->linkTree as $link_tree) {
      $link = $link_tree->link;
      $this->assertEquals($link->isExpanded(), $plugin->resolve($link));
    }
  }

  /**
   * @covers Drupal\graphql\Plugin\GraphQL\DataProducer\Menu\MenuLink\MenuLinkLabel::resolve
   */
  public function testMenuLinkLabel() {
    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'menu_link_label',
      'configuration' => []
    ]);
    foreach ($this->linkTree as $link_tree) {
      $link = $link_tree->link;
      $this->assertEquals($link->getTitle(), $plugin->resolve($link));
    }
  }

  /**
   * @covers Drupal\graphql\Plugin\GraphQL\DataProducer\Menu\MenuLink\MenuLinkUrl::resolve
   */
  public function testMenuLinkUrl() {
    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'menu_link_url',
      'configuration' => []
    ]);
    foreach ($this->linkTree as $link_tree) {
      $link = $link_tree->link;
      $this->assertEquals($link->getUrlObject(), $plugin->resolve($link));
    }
  }

}
