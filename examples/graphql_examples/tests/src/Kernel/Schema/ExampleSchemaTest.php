<?php

namespace Drupal\Tests\graphql_example\Kernel;

use Drupal\node\Entity\Node;
use Drupal\node\Entity\NodeType;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Drupal\user\Entity\User;

class ExampleSchemaTest extends GraphQLTestBase {

  /**
   * {@inheritdoc}
   */
  static $modules = ['graphql_examples'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() :void {
    parent::setUp();
    // Create the "article" node type since the schema relies on it.
    NodeType::create([
      'type' => 'article',
      'name' => 'Article',
    ]);

    // Create a test-server that uses the schema plugin defined in this module.
    $this->createTestServer('example', '/graphql');
  }

  /**
   * Test the example schema for article listing.
   */
  public function testExampleSchema() {
    // Create two authors.
    $userA = User::create([
      'name' => 'A'
    ]);
    $userA->save();

    $userB = User::create([
      'name' => 'B'
    ]);
    $userB->save();

    // Create three articles.
    Node::create([
      'type' => 'article',
      'title' => 'One',
      'uid' => $userA->id(),
    ])->save();

    Node::create([
      'type' => 'article',
      'title' => 'Two',
      'uid' => $userB->id(),
    ])->save();

    Node::create([
      'type' => 'article',
      'title' => 'Three',
      'uid' => $userA->id(),
    ])->save();

    // Execute the query and run assertions against its response content.
    $response = $this->query('{ articles { total, items { title, author } } }');
    $content = json_decode($response->getContent(), TRUE);
    $this->assertEquals([
      'data' => [
        'articles' => [
          'total' =>  3,
          'items' => [
            ['title' => 'ONE', 'author' => 'A'],
            ['title' => 'TWO', 'author' => 'B'],
            ['title' => 'THREE', 'author' => 'A'],
          ],
        ],
      ],
    ], $content);
  }
}
