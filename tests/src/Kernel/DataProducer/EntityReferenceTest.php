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

/**
 * Tests the entity_reference data producers.
 *
 * @group graphql
 */
class EntityReferenceTest extends GraphQLTestBase {
  use EntityReferenceTestTrait;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
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
    $this->referenced_node
      ->addTranslation('fr', ['title' => 'Dolor2 French'])
      ->save();

    $this->node = Node::create([
      'title' => 'Dolor',
      'type' => 'test1',
      'field_test1_to_test2' => $this->referenced_node->id(),
    ]);
    $this->node->save();
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Field\EntityReference::resolve
   */
  public function testResolveEntityReference(): void {
    $result = $this->executeDataProducer('entity_reference', [
      'entity' => $this->node,
      'field' => 'field_test1_to_test2',
      'access' => TRUE,
      'access_operation' => 'view',
    ]);
    $this->assertEquals($this->referenced_node->id(), reset($result)->id());
    $this->assertEquals('Dolor2', reset($result)->label());

    $result = $this->executeDataProducer('entity_reference', [
      'entity' => $this->node,
      'field' => 'field_test1_to_test2',
      'access' => TRUE,
      'access_operation' => 'view',
      'language' => 'fr',
    ]);
    $this->assertEquals($this->referenced_node->id(), reset($result)->id());
    $this->assertEquals('Dolor2 French', reset($result)->label());
  }

  /**
   * Tests that a given data producer returns an empty array.
   *
   * @dataProvider emptyResultsProvider
   */
  public function testEmptyResults(string $data_producer, array $contexts): void {
    $node = Node::create([
      'title' => 'Dolor',
      'type' => 'test1',
    ]);
    $node->save();
    $contexts['entity'] = $node;

    $result = $this->executeDataProducer($data_producer, $contexts);
    $this->assertIsArray($result);
    $this->assertEmpty($result);
  }

  /**
   * Data provider for testEmptyResults().
   */
  public function emptyResultsProvider(): array {
    return [
      // Test that an empty reference field returns an empty array.
      ['entity_reference', [
        'field' => 'field_test1_to_test2',
        'access' => TRUE,
        'access_operation' => 'view',
      ],
      ],
      // Test that an invalid field name returns an empty array.
      ['entity_reference', [
        'field' => 'does_not_exist',
        'access' => TRUE,
        'access_operation' => 'view',
      ],
      ],
      // Test that an invalid field type returns an empty array.
      ['entity_reference', [
        'field' => 'title',
        'access' => TRUE,
        'access_operation' => 'view',
      ],
      ],
      // Same test set for the entity_reference_revisions data producer.
      // Test that an empty reference field returns an empty array.
      ['entity_reference_revisions', [
        'field' => 'field_test1_to_test2',
        'access' => TRUE,
        'access_operation' => 'view',
      ],
      ],
      // Test that an invalid field name returns an empty array.
      ['entity_reference_revisions', [
        'field' => 'does_not_exist',
        'access' => TRUE,
        'access_operation' => 'view',
      ],
      ],
      // Test that an invalid field type returns an empty array.
      ['entity_reference_revisions', [
        'field' => 'title',
        'access' => TRUE,
        'access_operation' => 'view',
      ],
      ],
      // Same test set for the entity_reference_layout_revisions data producer.
      // Test that an empty reference field returns an empty array.
      ['entity_reference_layout_revisions', [
        'field' => 'field_test1_to_test2',
        'access' => TRUE,
        'access_operation' => 'view',
      ],
      ],
      // Test that an invalid field name returns an empty array.
      ['entity_reference_layout_revisions', [
        'field' => 'does_not_exist',
        'access' => TRUE,
        'access_operation' => 'view',
      ],
      ],
      // Test that an invalid field type returns an empty array.
      ['entity_reference_layout_revisions', [
        'field' => 'title',
        'access' => TRUE,
        'access_operation' => 'view',
      ],
      ],
    ];
  }

}
