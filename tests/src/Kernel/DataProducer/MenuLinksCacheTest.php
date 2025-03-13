<?php

namespace Drupal\Tests\graphql\Kernel\DataProducer;

use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\system\Entity\Menu;

/**
 * Tests that menu links cache metadata is correct.
 *
 * @group graphql
 */
class MenuLinksCacheTest extends GraphQLTestBase {

  /**
   * Test menu.
   *
   * @var \Drupal\system\Entity\Menu
   */
  protected $menu;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['graphql_menu_links_test'];

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('menu_link_content');

    $this->menu = Menu::create([
      'id' => 'access_test',
      'label' => 'Access test menu',
      'description' => 'Description text',
    ]);

    $this->menu->save();

    $link_options = [
      'title' => 'Menu link test',
      'provider' => 'graphql',
      'menu_name' => 'access_test',
      'link' => [
        // Only accessible by a user with name "super_admin".
        'uri' => 'internal:/graphql-protected',
      ],
      'description' => 'Test description',
    ];
    $link = MenuLinkContent::create($link_options);
    $link->save();
  }

  /**
   * Tests that the cache context is correctly set for different users.
   */
  public function testAccessCacheContext(): void {
    // Test as anonymous user, list of links must be empty.
    $result = $this->executeDataProducer('menu_links', [
      'menu' => $this->menu,
    ]);

    $this->assertEmpty($result);
    $this->assertSame('user', $this->fieldContext->getCacheContexts()[0]);

    // Test as super_admin user, list of links must contain the test link.
    $super_admin = $this->createUser(['access content'], 'super_admin');
    $this->setCurrentUser($super_admin);
    $result = $this->executeDataProducer('menu_links', [
      'menu' => $this->menu,
    ]);
    $menu_item = reset($result);
    $this->assertSame('Menu link test', $menu_item->link->getTitle());
    $this->assertSame('user', $this->fieldContext->getCacheContexts()[0]);
  }

}
