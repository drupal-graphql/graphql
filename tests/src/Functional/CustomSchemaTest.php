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
    $node = Node::create([
      'type' => 'article',
      'title' => 'giraffe',
    ]);
    $node->save();

    $nid = $node->id();
    $uuid = $node->uuid();
    $query = <<<GQL
{
  nodeByUuid(uuid: "$uuid") {
    entityId
  }
}
    
GQL;

    $body = $this->query($query);
    $data = json_decode($body, TRUE);
//    var_dump($data);
    $this->assertEquals([
      'data' => [
        'nodeByUuid' => [
          'entityId' => $nid,
        ],
      ],
    ], $data);
  }

}
