<?php

namespace Drupal\Tests\graphql_content\Kernel;

use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\Tests\graphql_core\Kernel\GraphQLFileTestBase;
use Drupal\user\Entity\Role;

/**
 * Test basic entity fields.
 *
 * @group graphql_content
 */
class RawFieldValueTest extends GraphQLFileTestBase {
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
    $this->installConfig(['text']);
    $this->installEntitySchema('node');

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

    Role::load('anonymous')
      ->grantPermission('access content')
      ->grantPermission('access user profiles')
      ->save();
    $options = ['type' => 'raw_value'];

    EntityViewMode::create(['id' => 'node.graphql', 'targetEntityType' => 'node'])->save();
    entity_get_display('node', 'test', 'graphql')
      ->setComponent('body', $options)
      ->setComponent('field_keywords', $options)
      ->setComponent('test', $options)
      ->save();
  }

  /**
   * Test if the basic fields are available on the interface.
   */
  public function testRenderedFields() {
    $body = [
      'value' => 'test',
      'format' => filter_default_format(),
    ];
    $keywords = ['a', 'b', 'c'];

    $node = $this->createNode([
      'title' => 'Test',
      'type' => 'test',
      'body' => $body,
      'field_keywords' => $keywords,
      'status' => 1,
    ]);

    $result = $this->executeQueryFile('rendered_fields.gql', [
      'path' => '/node/' . $node->id(),
    ]);

    $resultNode = NestedArray::getValue($result, ['data', 'route', 'entity']);
    $expected = [
      'body' => $body + ['summary' => null],
      'field_keywords' => ['value' => $keywords],
      'status' => ['value' => 1],
    ];
    $this->assertEquals($expected, $resultNode, 'Correct raw node values are returned.');
  }

}
