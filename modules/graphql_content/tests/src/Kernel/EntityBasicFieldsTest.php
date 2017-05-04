<?php

namespace Drupal\Tests\graphql_content\Kernel;

use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\Tests\graphql_core\Kernel\GraphQLFileTestBase;
use Drupal\user\Entity\Role;

/**
 * Test basic entity fields.
 *
 * @group graphql_content
 */
class EntityBasicFieldsTest extends GraphQLFileTestBase {
  use ContentTypeCreationTrait;
  use NodeCreationTrait;

  public static $modules = [
    'node',
    'field',
    'filter',
    'text',
    'graphql_content',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installConfig(['node']);
    $this->installConfig(['filter']);
    $this->installEntitySchema('node');

    $this->createContentType([
      'type' => 'test',
    ]);

    Role::load('anonymous')
      ->grantPermission('access content')
      ->grantPermission('access user profiles')
      ->save();
  }

  /**
   * Test if the basic fields are available on the interface.
   */
  public function testBasicFields() {
    $node = $this->createNode([
      'title' => 'Test',
      'type' => 'test',
      'status' => 1,
    ]);

    $result = $this->executeQueryFile('basic_fields.gql', [
      'path' => '/node/' . $node->id(),
    ]);

    $values = [
      'entityId' => $node->id(),
      'entityUuid' => $node->uuid(),
      'entityLabel' => $node->label(),
      'entityType' => $node->getEntityTypeId(),
      'entityBundle' => $node->bundle(),
      'entityRoute' => [
        'internalPath' => '/node/' . $node->id(),
        'aliasedPath' => '/node/' . $node->id(),
      ],
    ];

    $this->assertEquals($values, $result['data']['route']['node'], 'Content type Interface resolves basic entity fields.');
    $this->assertEquals($values, $result['data']['route']['node_test'], 'Content bundle Type resolves basic entity fields.');
  }

}
