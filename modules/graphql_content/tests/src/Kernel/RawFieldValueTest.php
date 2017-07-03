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
class RawFieldValueTest extends GraphQLFileTestBase {
  use ContentTypeCreationTrait;
  use NodeCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'node',
    'field',
    'filter',
    'text',
    'graphql_content',
    'link',
    'datetime',
  ];

  /**
   * Set of field types and values to use in the test.
   *
   * @var array
   */
  public static $fields = [
    'text' => ['a', 'b', 'c'],
    'boolean' => [TRUE, FALSE],
    'link' => [
      ['title' => 'Internal link', 'uri' => 'internal:/node/1'],
      ['title' => 'External link', 'uri' => 'http://drupal.org'],
    ],
    'integer' => [10, -5],
    'float' => [3.14145, -8.8],
    'decimal' => [10.5, -17.22],
    'datetime' => ['2017-01-01', '1900-01-01'],
    'timestamp' => [0, 300],
    'email' => ['test@test.com'],
    'string' => ['test', '123'],
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

    foreach (static::$fields as $type => $values) {
      $this->addField($type, "field_$type");
    }

    // TODO Test files and images.
    // $this->addField('file', 'field_file');
    // $this->addField('image', 'field_image');

    Role::load('anonymous')
      ->grantPermission('access content')
      ->grantPermission('access user profiles')
      ->save();

    $options = ['type' => 'raw_value'];
    EntityViewMode::create(['id' => 'node.graphql', 'targetEntityType' => 'node'])->save();

    $display = entity_get_display('node', 'test', 'graphql');
    $display->setComponent('body', $options);
    foreach (static::$fields as $type => $value) {
      $display->setComponent("field_$type", $options);
    }
    $display->save();
  }

  /**
   * Test if the basic fields are available on the interface.
   */
  public function testRawValues() {
    $values = [
      'title' => 'Test',
      'type' => 'test',
      'body' => [
        'value' => 'test',
        'format' => filter_default_format(),
        'summary' => 'test summary',
      ],
    ];
    foreach (static::$fields as $type => $value) {
      $values["field_$type"] = $value;
    }
    $node = $this->createNode($values);

    $result = $this->executeQueryFile('raw_field_values.gql', [
      'path' => '/node/' . $node->id(),
    ]);
    $resultNode = NestedArray::getValue($result, ['data', 'route', 'entity']);
    $expected = [
      'body' => $values['body'],
    ];
    foreach (static::$fields as $type => $fieldValues) {
      $expected['field' . ucfirst($type)] = array_map(function ($value) {
        return is_array($value) ? $value : ['value' => $value];
      }, $fieldValues);
    }
    $this->assertEquals($expected, $resultNode, 'Correct raw node values are returned.');
  }

  /**
   * Add a field to test content type.
   *
   * @param string $type
   *   Field type.
   * @param string $fieldName
   *   Field machine name.
   * @param string $label
   *   Label for the field.
   */
  protected function addField($type, $fieldName, $label = 'Label') {
    FieldStorageConfig::create([
      'field_name' => $fieldName,
      'entity_type' => 'node',
      'type' => $type,
      'cardinality' => -1,
    ])->save();
    FieldConfig::create([
      'entity_type' => 'node',
      'bundle' => 'test',
      'field_name' => $fieldName,
      'label' => $label,
    ])->save();
  }

}
