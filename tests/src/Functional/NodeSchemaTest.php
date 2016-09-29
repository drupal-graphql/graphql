<?php

namespace Drupal\Tests\graphql\Functional;

use Drupal\node\Entity\Node;

/**
 * Tests some custom schema.
 *
 * @group GraphQL
 */
class NodeSchemaTest extends QueryTestBase  {

  /**
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

  public function setUp() {
    parent::setUp();
    $node = Node::create([
      'type' => 'article',
      'title' => 'giraffe',
    ]);
    $node->save();
    $this->node = $node;
  }

  /**
   * @covers \Drupal\graphql\GraphQL\Field\Root\Entity\EntityByPathField
   */
  public function testNodeByPath() {
    $nid = $this->node->id();
    $query = <<<GQL
{
  entityByPath(path: "node/$nid") {
    entityId
    entityType {
      label
    }
  }
}
    
GQL;

    $body = $this->query($query);
    $data = json_decode($body, TRUE);
    $this->assertEquals([
      'data' => [
        'entityByPath' => [
          'entityId' => $nid,
          'entityType' => [
            'label' => 'Content',
          ],
        ],
      ],
    ], $data);
  }

  /**
   * @covers \Drupal\graphql\GraphQL\Field\Root\Entity\EntityByIdField
   */
  public function testNodeById() {
    $nid = $this->node->id();
    $query = <<<GQL
{
  nodeById(id: $nid) {
    entityId
  }
}
    
GQL;

    $body = $this->query($query);
    $data = json_decode($body, TRUE);
    $this->assertEquals([
      'data' => [
        'nodeById' => [
          'entityId' => (string) $nid,
        ],
      ],
    ], $data);
  }

  /**
   * @covers \Drupal\graphql\GraphQL\Field\Root\Entity\EntityByUuidField
   */
  public function testNodeByUuid() {
    $uuid = $this->node->uuid();
    $nid = $this->node->id();
    $query = <<<GQL
{
  nodeByUuid(uuid: "$uuid") {
    entityId
    entityType {
      label
    }
  }
}
    
GQL;

    $body = $this->query($query);
    $data = json_decode($body, TRUE);
    $this->assertEquals([
      'data' => [
        'nodeByUuid' => [
          'entityId' => $nid,
          'entityType' => [
            'label' => 'Content',
          ],
        ],
      ],
    ], $data);
  }

}
