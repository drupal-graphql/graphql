<?php

namespace Drupal\Tests\graphql_content\Kernel;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\Tests\graphql\Functional\QueryTestBase;
use Drupal\Tests\graphql_core\Traits\GraphQLFileTestTrait;

/**
 * Test if the cached schema gets updated automatically.
 *
 * @group graphql_content
 */
class SchemaCacheInvalidationTest extends QueryTestBase {
  use GraphQLFileTestTrait;

  public static $modules = [
    'node',
    'text',
    'graphql_content',
    'graphql_content_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->createContentType([
      'type' => 'test',
    ]);

    FieldStorageConfig::create([
      'field_name' => 'field_keywords',
      'entity_type' => 'node',
      'type' => 'text',
      'cardinality' => -1,
    ])->save();

    FieldConfig::create([
      'entity_type' => 'node',
      'bundle' => 'test',
      'field_name' => 'field_keywords',
      'label' => 'Keywords',
    ])->save();
  }

  /**
   * Test if a field change invalidates the schema cache.
   */
  public function testSchemaInvalidation() {
    // Retrieve the schema as string.
    $oldSchema = $this->query($this->getQuery('schema.gql'));

    $this->assertEquals($oldSchema, $this->query($this->getQuery('schema.gql')), 'Schema did not change yet.');

    // Attach a new field to the default node display.
    EntityViewDisplay::load('node.test.default')
      ->setComponent('field_keywords')
      ->save();


    // Retrieve the updated schema.
    $newSchema = $this->query($this->getQuery('schema.gql'));

    // Make sure it updated correctly.
    $this->assertNotEquals($oldSchema, $newSchema, 'The schema has changed.');
    $this->assertContains('fieldKeywords', $newSchema, 'Keywords field has been added.');
  }

}
