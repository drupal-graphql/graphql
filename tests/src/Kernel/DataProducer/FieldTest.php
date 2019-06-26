<?php

namespace Drupal\Tests\graphql\Kernel\DataProducer;

use Drupal\Tests\field\Traits\EntityReferenceTestTrait;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\node\NodeInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\node\Entity\NodeType;
use Drupal\node\Entity\Node;
use Drupal\user\UserInterface;
use Drupal\Tests\graphql\Traits\QueryResultAssertionTrait;

/**
 * Data producers Field test class.
 *
 * @group graphql
 */
class FieldTest extends GraphQLTestBase {
  use EntityReferenceTestTrait;
  use QueryResultAssertionTrait;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $this->entity = $this->getMockBuilder(NodeInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->entity_interface = $this->getMockBuilder(EntityInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $this->user = $this->getMockBuilder(UserInterface::class)
      ->disableOriginalConstructor()
      ->getMock();

    $content_type1 = NodeType::create([
      'type' => 'test1',
      'name' => 'ipsum1',
    ]);
    $content_type1->save();

    $content_type2 = NodeType::create([
      'type' => 'test2',
      'name' => 'ipsum2',
    ]);
    $content_type2->save();

    $this->createEntityReferenceField('node', 'test1', 'field_test1_to_test2', 'test1 lable', 'node', 'default', [], FieldStorageDefinitionInterface::CARDINALITY_UNLIMITED);

    $this->referenced_node = Node::create([
      'title' => 'Dolor2',
      'type' => 'test2',
    ]);
    $this->referenced_node->save();

    $this->node = Node::create([
      'title' => 'Dolor',
      'type' => 'test1',
      'field_test1_to_test2' => $this->referenced_node->id()
    ]);
    $this->node->save();
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Field\EntityReference::resolve
   */
  public function testResolveEntityReference() {
    $result = $this->executeDataProducer('entity_reference', [
      'entity' => $this->node,
      'field' => 'field_test1_to_test2',
    ]);

    $this->assertEquals($this->referenced_node->id(), reset($result)->id());
  }

}
