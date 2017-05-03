<?php

namespace Drupal\Tests\graphql_content\Kernel;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\Tests\graphql_core\Kernel\GraphQLFileTest;

/**
 * Test if the cached schema gets updated automatically.
 */
class SchemaCacheInvalidationTest extends GraphQLFileTest {
  use ContentTypeCreationTrait;
  use NodeCreationTrait;

  public static $modules = [
    'node',
    'field',
    'filter',
    'text',
    'graphql_content',
    'graphql_content_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installConfig(['node']);
    $this->installConfig(['filter']);
    $this->installConfig(['text']);
    $this->installEntitySchema('node');

    $this->createContentType([
      'type' => 'test',
    ]);
  }

  /**
   * Test if the basic fields are available on the interface.
   */
  public function testSchemaInvalidation() {
    $pre_update = $this->executeQueryFile('schema.gql');

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

    EntityViewDisplay::load('node.test.default')
      ->setComponent('field_keywords')
      ->save();

    $post_update = $this->executeQueryFile('schema.gql');

    $this->assertNotEquals($pre_update, $post_update, 'The schema has changed.');

  }

}
