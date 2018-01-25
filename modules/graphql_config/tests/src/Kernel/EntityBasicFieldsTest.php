<?php

namespace Drupal\Tests\graphql_config\Kernel;

use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\Tests\graphql_core\Kernel\GraphQLFileTestBase;

/**
 * Test basic entity fields.
 *
 * @group graphql_config
 */
class EntityBasicFieldsTest extends GraphQLFileTestBase {
  use ContentTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'field',
    'filter',
    'text',
    'graphql_config',
  ];

  /**
   * @var \Drupal\node\Entity\NodeType
   */
  protected $content_type;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installConfig(['node', 'filter']);
    $this->content_type = $this->createContentType(['type' => 'test']);

    $this->container->get('config.factory')->getEditable('graphql_config.schema')
      ->set('types', [
        'node_type' => [
          'exposed' => TRUE,
        ],
      ])->save();
  }

  /**
   * Test if the basic fields are available on the interface.
   */
  public function testBasicFields() {
    $result = $this->executeQueryFile('basic_fields.gql', [
      'id' => 'test',
    ]);

    $values = [
      'entityId' => $this->content_type->id(),
      'entityUuid' => $this->content_type->uuid(),
      'entityLabel' => $this->content_type->label(),
      'entityType' => $this->content_type->getEntityTypeId(),
      'entityBundle' => $this->content_type->bundle(),
    ];

    $this->assertEquals($values, $result['data']['nodeTypeById']);
  }

}
