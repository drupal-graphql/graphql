<?php

namespace Drupal\Tests\graphql_twig\Kernel;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\graphql\QueryResult;
use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\user\Entity\User;
use Prophecy\Argument;

/**
 * Tests that test GraphQL theme integration on module level.
 */
class EntityRenderTest extends ThemeTest {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'datetime',
    'field',
    'text',
    'system',
    'node',
    'user',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig(['system', 'user', 'node']);
    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installSchema('system', ['sequences']);
    NodeType::create([
      'type' => 'article',
      'name' => 'Article',
    ])->save();
  }

  public function testNodeRender() {
    $node = Node::create([
      'title' => 'Test',
      'type' => 'article',
      'uid' => User::create([
        'name' => 'test',
      ])->save(),
    ]);
    $node->save();

    $this->processor->processQuery(Argument::any(), ['node' => '1'])
      ->willReturn(new QueryResult([
        'data' => [
          'node' => [
            'title' => 'Test',
          ],
        ],
      ], new CacheableMetadata()));

    $viewBuilder = $this->container->get('entity_type.manager')->getViewBuilder('node');
    $build = $viewBuilder->view($node);
    $result = $this->render($build);
    $this->assertContains('<h1>Test</h1>', $result);
  }

}
