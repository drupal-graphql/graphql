<?php

namespace Drupal\Tests\graphql_mutation\Kernel;

use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\Tests\graphql\Kernel\GraphQLFileTestBase;
use Drupal\user\Entity\Role;

/**
 * Test entity deletion.
 *
 * @group graphql_mutation
 */
class DeleteEntityTest extends GraphQLFileTestBase {
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
      ->grantPermission('delete any test content')
      ->save();
  }

  /**
   * Test deletion.
   */
  public function testDeletion() {
    $node = $this->createNode(['type' => 'test']);
    $result = $this->executeQueryFile('delete.gql', ['id' => $node->id()]);
    $entity = $result['data']['deleteNode']['entity'];
    $errors = $result['data']['deleteNode']['errors'];

    $this->assertEquals($node->id(), $entity['entityId'], 'Deleted entity with correct id.');
    $this->assertEmpty($errors, 'Entity deletion succeeded without any errors.');
  }

  /**
   * Test deletion without the necessary permissions
   */
  public function testDeletionWithoutPermission() {
    Role::load('anonymous')
      ->revokePermission('delete any test content')
      ->save();

    $node = $this->createNode(['type' => 'test']);
    $result = $this->executeQueryFile('delete.gql', ['id' => $node->id()]);
    $errors = $result['data']['deleteNode']['errors'];

    $this->assertNotEmpty($errors, 'Failed to delete entity.');
    $this->assertEquals(['You do not have the necessary permissions to delete this entity.'], $errors, 'Failed to delete entity due to missing permissions.');
  }

  /**
   * Test deletion of a non-existent entity.
   */
  public function testDeletionOfNonExistentEntity() {
    $result = $this->executeQueryFile('delete.gql', ['id' => '123']);
    $errors = $result['data']['deleteNode']['errors'];

    $this->assertNotEmpty($errors, 'Failed to delete entity.');
    $this->assertEquals(['The requested entity could not be loaded.'], $errors, 'Failed to delete non-existent entity.');
  }

}
