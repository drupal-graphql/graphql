<?php

namespace Drupal\Tests\graphql_mutation\Kernel;

use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\Tests\graphql\Kernel\GraphQLFileTestBase;
use Drupal\user\Entity\Role;

/**
 * Test entity update.
 *
 * @group graphql_mutation
 */
class UpdateEntityTest extends GraphQLFileTestBase {
  use ContentTypeCreationTrait;
  use NodeCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'field',
    'text',
    'filter',
    'graphql_core',
    'graphql_mutation',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig('node');
    $this->installConfig('filter');
    $this->installEntitySchema('node');
    $this->installSchema('node', 'node_access');
    $this->createContentType(['type' => 'test']);

    Role::load('anonymous')
      ->grantPermission('access content')
      ->grantPermission('edit any test content')
      ->save();
  }

  /**
   * Test update with a simple text field.
   */
  public function testUpdateWithBodyField() {
    $node = $this->createNode([
      'type' => 'test',
      'title' => 'Test node (original)',
      'body' => [
        'value' => 'I am fine with my body.',
      ],
    ]);

    $values = [
      'title' => 'Test node (updated)',
      'body' => [
        'value' => 'I am fine with my body, still.',
      ],
    ];

    $result = $this->executeQueryFile('update.gql', ['id' => $node->id(), 'input' => $values]);
    $entity = $result['data']['updateNodeTest']['entity'];
    $errors = $result['data']['updateNodeTest']['errors'];
    $violations = $result['data']['updateNodeTest']['violations'];

    $this->assertEquals($values['title'], $entity['entityLabel'], 'Update entity with correct title.');
    $this->assertEquals($values['body']['value'], $entity['body']['value'], 'Update entity with correct body value.');
    $this->assertEmpty($errors, 'Entity update succeeded without any errors.');
    $this->assertEmpty($violations, 'Entity update succeeded without any constraint violations.');
  }

  /**
   * Test update with missing values for a required field.
   */
  public function testUpdateWithViolations() {
    $node = $this->createNode([
      'type' => 'test',
      'title' => 'Test node (original)',
    ]);

    $result = $this->executeQueryFile('update.gql', ['id' => $node->id(), 'input' => [
      'title' => NULL,
    ]]);

    $errors = $result['data']['updateNodeTest']['errors'];
    $violations = $result['data']['updateNodeTest']['violations'];

    $this->assertEmpty($errors, 'No errors were thrown.');
    $this->assertNotEmpty($violations, 'Entity update failed due to constraint violations.');
    $this->assertEquals([[
      'path' => 'title',
      'message' => 'This value should not be null.',
    ]], $violations, 'Entity update failed due to constraint violations.');
  }

  /**
   * Test update without the necessary permissions
   */
  public function testUpdateWithoutPermission() {
    $node = $this->createNode([
      'type' => 'test',
      'title' => 'Test node (original)',
    ]);

    Role::load('anonymous')
      ->revokePermission('edit any test content')
      ->save();

    $result = $this->executeQueryFile('update.gql', ['id' => $node->id(), 'input' => []]);
    $errors = $result['data']['updateNodeTest']['errors'];

    $this->assertNotEmpty($errors, 'Failed to update entity.');
    $this->assertEquals(['You do not have the necessary permissions to update this test.'], $errors, 'Failed to update entity due to missing permissions.');
  }

}
