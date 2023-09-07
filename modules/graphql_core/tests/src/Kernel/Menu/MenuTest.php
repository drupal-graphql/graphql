<?php

namespace Drupal\Tests\graphql_core\Kernel\Menu;

use Drupal\Core\Menu\MenuTreeParameters;
use Drupal\menu_link_content\Entity\MenuLinkContent;
use Drupal\Tests\graphql_core\Kernel\GraphQLCoreTestBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Test access to menu items.
 *
 * @group graphql_core
 */
class MenuTest extends GraphQLCoreTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'menu_link_content',
    'link',
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
    $this->assertIsObject($menu);

    /** @var \Drupal\Core\Menu\MenuLinkTreeInterface $menuTree */
    $menuTree = $this->container->get('menu.link_tree');
    $this->assertEquals(count($menuTree->load('test', new MenuTreeParameters())), 3);

    /** @var \Symfony\Component\HttpKernel\HttpKernelInterface $httpKernel */
    $httpKernel = $this->container->get('http_kernel');

    $this->assertEquals($httpKernel->handle(Request::create('/graphql/test/accessible'))->getStatusCode(), 200);
    $this->assertEquals($httpKernel->handle(Request::create('/graphql/test/inaccessible'))->getStatusCode(), 403);
  }

  /**
   * Test menu tree data retrieval.
   */
  public function testMenuTree() {
    $metadata = $this->defaultCacheMetaData();
    $metadata->addCacheTags(['config:system.menu.test']);

    $this->assertResults(
      $this->getQueryFromFile('menu.gql'),
      [],
      [
        'info' => [
          'name' => 'Test menu',
          'description' => 'Menu for testing GraphQL menu access.',
        ],
        'menu' => [
          'links' => [
            0 => [
              'label' => 'Accessible',
              'route' => [
                'path' => '/graphql/test/accessible',
                'routed' => TRUE,
              ],
              'attribute' => NULL,
              'links' => [
                0 => [
                  'label' => 'Nested A',
                  'attribute' => NULL,
                  'route' => [
                    'path' => '/graphql/test/accessible',
                    'routed' => TRUE,
                  ],
                ],
                1 => [
                  'label' => 'Nested B',
                  'route' => [
                    'path' => '/graphql/test/accessible',
                    'routed' => TRUE,
                  ],
                  'attribute' => NULL,
                ],
              ],
            ],
            1 => [
              'label' => 'Drupal',
              'route' => [
                'path' => 'http://www.drupal.org',
                'routed' => FALSE,
              ],
              'attribute' => NULL,
              'links' => [],
            ],
          ],
        ],
      ],
      $metadata
    );
  }
}
