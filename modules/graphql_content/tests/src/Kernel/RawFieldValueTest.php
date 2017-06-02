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

    // TODO Question: So the goal is to create a field formatter that can be
    //                attached to each and every possible field type and then
    //                set this formatter via the $options parameter of
    //                EntityDisplayInterface::setComponent below?

    EntityViewMode::create(['id' => 'node.graphql', 'targetEntityType' => 'node'])->save();
    entity_get_display('node', 'test', 'graphql')
      ->setComponent('body')
      ->setComponent('field_keywords')
      ->setComponent('test')
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

    // TODO Execute query and check return value once ::setUp is finished.
  }

}
