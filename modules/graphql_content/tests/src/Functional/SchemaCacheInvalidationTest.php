<?php

namespace Drupal\Tests\graphql_content\Functional;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
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
   * Test if a field change invalidates the schema cache.
   */
  public function testSchemaInvalidation() {
    // Retrieve the schema as string.
    $schemaConfig = $this->container->get('graphql_content.schema_config');

    // Check if configuration is loaded correctly.
    $this->assertTrue($schemaConfig->isEntityTypeExposed('node'));
    $this->assertTrue($schemaConfig->isEntityBundleExposed('node', 'test'));
    $this->assertEquals('graphql', $schemaConfig->getExposedViewMode('node', 'test'));

    $oldSchema = $this->query($this->getQuery('schema.gql'));
    $this->assertEquals($oldSchema, $this->query($this->getQuery('schema.gql')), 'Schema did not change yet.');

    // Attach a new field to the default node display.
    EntityViewDisplay::load('node.test.graphql')
      ->setComponent('body')
      ->save();

    // Retrieve the updated schema.
    $newSchema = $this->query($this->getQuery('schema.gql'));

    // Make sure it updated correctly.
    $this->assertNotEquals($oldSchema, $newSchema, 'The schema has changed.');
    $this->assertContains('body', $newSchema, 'Body field has been added.');
  }

}
