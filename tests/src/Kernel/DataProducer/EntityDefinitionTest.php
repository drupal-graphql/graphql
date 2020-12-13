<?php

namespace Drupal\Tests\graphql\Kernel\DataProducer;

use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Drupal\node\Entity\NodeType;
use Drupal\graphql\GraphQL\ResolverBuilder;

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

    // Create a form display.
    $form_display = EntityFormDisplay::create([
      'targetEntityType' => 'node',
      'bundle' => 'article',
      'mode' => 'default',
    ]);
    $form_display->save();

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

      enum FieldTypes {
        ALL
        BASE_FIELDS
        FIELD_CONFIG
      }

      type Query {
        entityDefinition(
          entity_type: String!
          bundle: String
          field_types: FieldTypes
        ): EntityDefinition
      }
GQL;

    $this->setUpSchema($schema);

    $registry = $this->registry;
    $builder = new ResolverBuilder();

    // Entity definition query.
    $registry->addFieldResolver('Query', 'entityDefinition',
      $builder->produce('entity_definition', [
        'entity_type' => $builder->fromArgument('entity_type'),
        'bundle' => $builder->fromArgument('bundle'),
        'field_types' => $builder->fromArgument('field_types'),
      ])
    );
    // Entity definition fields.
    $registry->addFieldResolver('EntityDefinition', 'label',
      $builder->produce('entity_definition_label', [
        'entity_definition' => $builder->fromParent(),
      ])
    );
    $registry->addFieldResolver('EntityDefinition', 'fields',
      $builder->produce('entity_definition_fields', [
        'entity_definition' => $builder->fromParent(),
        'bundle_context' => $builder->fromContext('bundle'),
        'field_types_context' => $builder->fromContext('field_types'),
      ])
    );
    $registry->addFieldResolver('EntityDefinitionField', 'id',
      $builder->produce('entity_definition_field_id', [
        'entity_definition_field' => $builder->fromParent(),
      ])
    );
    $registry->addFieldResolver('EntityDefinitionField', 'label',
      $builder->produce('entity_definition_field_label', [
        'entity_definition_field' => $builder->fromParent(),
      ])
    );
    $registry->addFieldResolver('EntityDefinitionField', 'description',
      $builder->produce('entity_definition_field_description', [
        'entity_definition_field' => $builder->fromParent(),
      ])
    );
    $registry->addFieldResolver('EntityDefinitionField', 'type',
      $builder->produce('entity_definition_field_type', [
        'entity_definition_field' => $builder->fromParent(),
      ])
    );
    $registry->addFieldResolver('EntityDefinitionField', 'required',
      $builder->produce('entity_definition_field_required', [
        'entity_definition_field' => $builder->fromParent(),
      ])
    );
    $registry->addFieldResolver('EntityDefinitionField', 'multiple',
      $builder->produce('entity_definition_field_multiple', [
        'entity_definition_field' => $builder->fromParent(),
      ])
    );
    $registry->addFieldResolver('EntityDefinitionField', 'maxNumItems',
      $builder->produce('entity_definition_field_max_num_items', [
        'entity_definition_field' => $builder->fromParent(),
      ])
    );
    $registry->addFieldResolver('EntityDefinitionField', 'status',
      $builder->produce('entity_definition_field_status', [
        'entity_definition_field' => $builder->fromParent(),
      ])
    );
    $registry->addFieldResolver('EntityDefinitionField', 'defaultValue',
      $builder->produce('entity_definition_field_default_value', [
        'entity_definition_field' => $builder->fromParent(),
      ])
    );
    $registry->addFieldResolver('EntityDefinitionField', 'defaultValues',
      $builder->produce('entity_definition_field_additional_default_value', [
        'entity_definition_field' => $builder->fromParent(),
      ])
    );
    $registry->addFieldResolver('EntityDefinitionField', 'isReference',
      $builder->produce('entity_definition_field_reference', [
        'entity_definition_field' => $builder->fromParent(),
      ])
    );
    $registry->addFieldResolver('EntityDefinitionField', 'isHidden',
      $builder->produce('entity_definition_field_hidden', [
        'entity_definition_field' => $builder->fromParent(),
        'entity_form_display_context' => $builder->fromContext('entity_form_display'),
      ])
    );
    $registry->addFieldResolver('EntityDefinitionField', 'weight',
      $builder->produce('entity_definition_field_weight', [
        'entity_definition_field' => $builder->fromParent(),
        'entity_form_display_context' => $builder->fromContext('entity_form_display'),
      ])
    );
    $registry->addFieldResolver('EntityDefinitionField', 'settings',
      $builder->produce('translatable_entity_definition_field_settings', [
        'entity_definition_field' => $builder->fromParent(),
        'entity_form_display_context' => $builder->fromContext('entity_form_display'),
      ])
    );
  }

  /**
   * Tests that retrieving an entity definition works.
   */
  public function testEntityDefinition() {
    $query = <<<GQL
      query {
        entityDefinition(entity_type: "node", bundle: "article") {
          label
          fields {
            id
            label
            description
            type
            required
            multiple
            maxNumItems
            status
            defaultValue
            isReference
            isHidden
            weight
            settings
          }
        }
      }
GQL;

    $this->assertResults($query, [], ['entityDefinition' => ['label' => 'Content']]);
  }

}
