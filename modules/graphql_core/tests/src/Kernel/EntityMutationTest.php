<?php

namespace Drupal\Tests\graphql_core\Kernel;

use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\user\Entity\Role;

/**
 * Test entity mutation support in GraphQL.
 *
 * @group graphql_core
 */
class EntityMutationTest extends GraphQLFileTestBase {
  use NodeCreationTrait;
  use ContentTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'field',
    'filter',
    'text',
    'taxonomy',
    'graphql_mutation',
    'graphql_content_mutation',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installConfig(['node', 'filter']);
    $this->installSchema('node', 'node_access');
    $this->createContentType(['type' => 'article']);

  }

  /**
   * Test a successful node creation.
   */
  public function testCreateEntityMutation() {
    Role::load('anonymous')
      ->grantPermission('bypass node access')
      ->save();

    $result = $this->executeQueryFile('entity_mutation.gql', [
      'node' => [
        'title' => 'Test',
      ],
    ]);

    $this->assertEmpty($result['data']['create']['errors'], 'There where no errors.');
    $this->assertEmpty($result['data']['create']['violations'], 'There where no violations.');

    $count = $this->container->get('entity_type.manager')->getStorage('node')->getQuery()->count()->execute();
    $this->assertEquals('1', $count, 'One node has been created.');

    $this->assertNotEmpty($result['data']['create']['entity']['uuid'], 'The created entity has an uuid.');

    $node = $this->container->get('entity.repository')->loadEntityByUuid('node', $result['data']['create']['entity']['uuid']);
    $this->assertEquals('Test', $node->label(), 'The created node has the expected title.');
  }

  /**
   * Test entity creation permission.
   */
  public function testCreateEntityMutationNoPermitted() {

    $result = $this->executeQueryFile('entity_mutation.gql', [
      'node' => [
        'title' => 'Test',
      ],
    ]);

    $this->assertNotEmpty($result['data']['create']['errors'], 'Errors have been reported.');

    $count = $this->container->get('entity_type.manager')->getStorage('node')->getQuery()->count()->execute();
    $this->assertEquals('0', $count, 'No node has been created.');
  }

  /**
   * Test entity creation constraints.
   */
  public function testCreateEntityMutationConstraints() {
    Role::load('anonymous')
      ->grantPermission('bypass node access')
      ->save();

    $result = $this->executeQueryFile('entity_mutation.gql', [
      'node' => [
        'title' => '',
      ],
    ]);

    $this->assertNotEmpty($result['data']['create']['violations'], 'Violations have been reported.');
    $this->assertEquals($result['data']['create']['violations'][0]['path'], 'title', 'The violation refers to the missing title field.');

    $count = $this->container->get('entity_type.manager')->getStorage('node')->getQuery()->count()->execute();
    $this->assertEquals('0', $count, 'No node has been created.');
  }

  /**
   * Test creation of a multi-value field.
   */
  public function testCreateMultiValueField() {
    Role::load('anonymous')
      ->grantPermission('bypass node access')
      ->save();

    $result = $this->executeQueryFile('entity_mutation.gql', [
      'node' => [
        'title' => 'Test',
        'body' => [
          'value' => 'Test',
          'format' => 'plain_text',
        ],
      ],
    ]);

    $this->assertEmpty($result['data']['create']['errors'], 'There where no errors.');
    $this->assertEmpty($result['data']['create']['violations'], 'There where no violations.');

    $count = $this->container->get('entity_type.manager')->getStorage('node')->getQuery()->count()->execute();
    $this->assertEquals('1', $count, 'One node has been created.');

    $this->assertNotEmpty($result['data']['create']['entity']['uuid'], 'The created entity has an uuid.');

    $node = $this->container->get('entity.repository')->loadEntityByUuid('node', $result['data']['create']['entity']['uuid']);
    $this->assertEquals('Test', $node->body->value, 'The created node has the expected body.');
    $this->assertEquals('plain_text', $node->body->format, 'The created node has the expected filter format.');
  }

}
