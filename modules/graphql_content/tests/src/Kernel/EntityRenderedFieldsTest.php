<?php

namespace Drupal\Tests\graphql_content\Kernel;

use Drupal\Component\Utility\NestedArray;
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
class EntityRenderedFieldsTest extends GraphQLFileTestBase {
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

    EntityViewMode::create(['id' => 'node.graphql', 'targetEntityType' => 'node'])->save();
    entity_get_display('node', 'test', 'graphql')
      ->setComponent('body')
      ->setComponent('field_keywords')
      ->setComponent('test')
      ->save();

    Role::load('anonymous')
      ->grantPermission('access content')
      ->grantPermission('access user profiles')
      ->save();
  }

  /**
   * Test if the basic fields are available on the interface.
   */
  public function testRenderedFields() {
    $node = $this->createNode([
      'title' => 'Test',
      'type' => 'test',
      'body'      => [
        'value' => 'test',
        'format' => filter_default_format(),
      ],
      'field_keywords' => ['a', 'b', 'c'],
      'status' => 1,
    ]);

    $result = $this->executeQueryFile('rendered_fields.gql', [
      'path' => '/node/' . $node->id(),
    ]);

    $node = NestedArray::getValue($result, ['data', 'route', 'entity']);

    $this->assertNotNull($node, 'A node has been retrieved.');

    $this->assertEquals('<p>test</p>', $node['body'], 'Body field retrieved properly.');
    $this->assertEquals([
      '<p>a</p>',
      '<p>b</p>',
      '<p>c</p>',
    ], $node['fieldKeywords'], 'Multi value rendered field works.');

    $this->assertEquals('This is a test.', $node['test'], 'Extra field is available.');
  }

}
