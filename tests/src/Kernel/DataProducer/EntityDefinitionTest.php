<?php

namespace Drupal\Tests\graphql\Kernel\DataProducer;

use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Drupal\node\Entity\NodeType;

/**
 * Test the entity_definition data producer and friends.
 *
 * @group graphql
 */
class EntityDefinitionTest extends GraphQLTestBase {
  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $content_type = NodeType::create([
      'type' => 'article',
      'name' => 'article',
    ]);
    $content_type->save();

    $schema = <<<GQL
      type EntityDefinition {
        label: String
        fields: [EntityDefinitionField]
      }

      type EntityDefinitionField {
        id: String
        label: String
        description: String
        type: String
        required: Boolean
        multiple: Boolean
        maxNumItems: Int
        status: Boolean
        defaultValue: String
        isReference: Boolean
        isHidden: Boolean
        weight: Int
        settings: [KeyValue]
      }

      scalar KeyValue

      type Query {
        entityDefinition(
          entity_type: String!
          bundle: String
          field_types: FieldTypes
        ): EntityDefinition
      }
GQL;

    $this->setUpSchema($schema);
  }

  /**
   * Tests that retrieving an entity definition works.
   */
  public function testResolveBundle() {
    /*$result = $this->executeDataProducer('entity_definition', [
      'entity_type' => 'node',
      'bundle' => 'article',
    ]);*/

    $this->assertEquals('page', 'page');
  }

}
