<?php

namespace Drupal\Tests\graphql_core\Kernel\Menu;

use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\Tests\graphql\Kernel\GraphQLFileTestBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Test access to menu items.
 *
 * @group graphql_menu
 */
class MenuTest extends GraphQLFileTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'system',
    'menu_link_content',
    'link',
    'graphql_core',
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

    $externalLink = MenuLinkContent::create([
      'title' => 'Drupal',
      'link' => ['uri' => 'http://www.drupal.org'],
      'menu_name' => 'test',
      'external' => 1,
      'enabled' => 1,
      'weight' => 5,
    ]);

    $externalLink->save();

    /** @var \Drupal\Core\Menu\MenuLinkManagerInterface $menuLinkManager */
    $menuLinkManager = $this->container->get('plugin.manager.menu.link');
    $menuLinkManager->rebuild();
  }

  /**
   * Test if the test setup itself is successful.
   */
  public function testTestSetup() {
    /** @var \Drupal\Core\Menu\MenuTreeStorageInterface $menuStorage */
    $menuStorage = $this->container->get('entity_type.manager')->getStorage('menu');
    $menu = $menuStorage->load('test');
    $this->assertTrue($menu);

    /** @var \Drupal\Core\Menu\MenuLinkTreeInterface $menuTree */
    $menuTree = $this->container->get('menu.link_tree');
    $this->assertEquals(count($menuTree->load('test', new MenuTreeParameters())), 3);

    /** @var \Symfony\Component\HttpKernel\HttpKernelInterface $httpKernel */
    $httpKernel = $this->container->get('http_kernel');

    $this->assertEquals($httpKernel->handle(Request::create('/graphql/test/accessible'))->getStatusCode(), 200);
    $this->assertEquals($httpKernel->handle(Request::create('/graphql/test/inaccessible'))->getStatusCode(), 403);
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

    $inaccessibleChildren = $result['data']['menu']['links'][1]['links'];
    $this->assertEmpty($inaccessibleChildren, 'Inaccessible items do not expose children.');

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
