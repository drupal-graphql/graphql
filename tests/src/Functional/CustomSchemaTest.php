<?php

namespace Drupal\Tests\graphql\Functional;

use Drupal\node\Entity\Node;

/**
 * Tests some custom schema.
 *
 * @group GraphQL
 */
class CustomSchemaTest extends QueryTestBase  {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['graphql_test_custom_schema'];

  public function testSchema() {
    Node::create([
      'type' => 'article',
      'title' => 'giraffe',
    ])->save();

    $query = <<<GQL
{
  articleById(id: 1) {
    title
  }
}
    
GQL;

    $body = $this->query($query);
    $data = json_decode($body, TRUE);
    $this->assertEquals([
      'data' => [
        'articleById' => [
          'title' => 'giraffe',
        ],
      ],
    ], $data);
  }

}
