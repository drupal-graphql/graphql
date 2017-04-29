<?php

namespace Drupal\Tests\graphql_menu;

use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\Tests\graphql_core\GraphQLFileTest;
use Symfony\Component\HttpFoundation\Request;

/**
 * Test access to menu items.
 */
class MenuTest extends GraphQLFileTest {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'system',
    'menu_link_content',
    'link',
    'graphql_menu',
    'graphql_test_menu',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('menu_link_content');
    $this->installConfig('menu_link_content');
    $this->installConfig('graphql_test_menu');

    $external_link = MenuLinkContent::create([
      'title' => 'Drupal',
      'link' => ['uri' => 'http://www.drupal.org'],
      'menu_name' => 'test',
      'external' => 1,
      'enabled' => 1,
      'weight' => 5,
    ]);

    $external_link->save();

    /** @var \Drupal\Core\Menu\MenuLinkManagerInterface $menu_link_manager */
    $menu_link_manager = $this->container->get('plugin.manager.menu.link');
    $menu_link_manager->rebuild();
  }

  /**
   * Test if the test setup itself is successful.
   */
  public function testTestSetup() {
    /** @var \Drupal\Core\Menu\MenuTreeStorageInterface $menu_storage */
    $menu_storage = $this->container->get('entity_type.manager')->getStorage('menu');
    $menu = $menu_storage->load('test');
    $this->assertTrue($menu);

    /** @var \Drupal\Core\Menu\MenuLinkTreeInterface $menu_tree */
    $menu_tree = $this->container->get('menu.link_tree');
    $this->assertEquals(count($menu_tree->load('test', new MenuTreeParameters())), 3);

    /** @var \Symfony\Component\HttpKernel\HttpKernelInterface $http_kernel */
    $http_kernel = $this->container->get('http_kernel');

    $this->assertEquals($http_kernel->handle(Request::create('/graphql/test/accessible'))->getStatusCode(), 200);
    $this->assertEquals($http_kernel->handle(Request::create('/graphql/test/inaccessible'))->getStatusCode(), 403);
  }

  /**
   * Test if menu information is returned by GraphQL.
   */
  public function testMenuInfo() {
    $result = $this->executeQueryFile('menu.gql');

    $this->assertArrayHasKey('data', $result);

    $this->assertArraySubset([
      'info' => [
        'name' => 'Test menu',
        'description' => 'Menu for testing GraphQL menu access.',
      ],
    ], $result['data'], "Menu contains correct title and description.");
  }

  /**
   * Test menu tree data retrieval.
   */
  public function testMenuTree() {

    $result = $this->executeQueryFile('menu.gql');

    $this->assertArrayHasKey('data', $result);

    $this->assertArraySubset([
      'menu' => [
        'links' => [
          0 => [
            'label' => 'Accessible',
            'route' => [
              'path' => '/graphql/test/accessible',
              'routed' => TRUE,
            ],
          ],
        ],
      ],
    ], $result['data'], 'Accessible root item is returned.');

    $this->assertArraySubset([
      'menu' => [
        'links' => [
          0 => [
            'links' => [
              0 => [
                'label' => 'Nested A',
                'route' => [
                  'path' => '/graphql/test/accessible',
                  'routed' => TRUE,
                ],
              ],
            ],
          ],
        ],
      ],
    ], $result['data'], 'Accessible nested item A is returned.');

    $this->assertArraySubset([
      'menu' => [
        'links' => [
          0 => [
            'links' => [
              1 => [
                'label' => 'Nested B',
                'route' => [
                  'path' => '/graphql/test/accessible',
                  'routed' => TRUE,
                ],
              ],
            ],
          ],
        ],
      ],
    ], $result['data'], 'Accessible nested item B is returned.');

    $this->assertArraySubset([
      'menu' => [
        'links' => [
          1 => [
            'label' => 'Inaccessible',
            'route' => [
              'path' => '/',
              'routed' => TRUE,
            ],
          ],
        ],
      ],
    ], $result['data'], 'Inaccessible root item is obfuscated.');

    $inaccessible_children = $result['data']['menu']['links'][1]['links'];
    $this->assertEmpty($inaccessible_children, 'Inaccessible items do not expose children.');

    $this->assertArraySubset([
      'menu' => [
        'links' => [
          2 => [
            'label' => 'Drupal',
            'route' => [
              'path' => 'http://www.drupal.org',
              'routed' => FALSE,
            ],
          ],
        ],
      ],
    ], $result['data'], 'External menu link is included properly.');
  }

}
