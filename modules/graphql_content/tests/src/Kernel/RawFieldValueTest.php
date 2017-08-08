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
    'image',
    'file',
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
    $this->installEntitySchema('file');

    $this->createContentType([
      'type' => 'test',
    ]);

    $this->addField('text', "field_text");
    $this->addField('boolean', "field_boolean");
    $this->addField('link', "field_link");
    $this->addField('integer', "field_integer");
    $this->addField('float', "field_float");
    $this->addField('decimal', "field_decimal");
    $this->addField('datetime', "field_datetime");
    $this->addField('timestamp', "field_timestamp");
    $this->addField('email', "field_email");
    $this->addField('string', "field_string");

    $this->addField('entity_reference', 'field_reference');
    $this->addField('file', 'field_file');
    $this->addField('image', 'field_image');

    Role::load('anonymous')
      ->grantPermission('access content')
      ->grantPermission('access user profiles')
      ->save();

    $options = ['type' => 'graphql_raw_value'];
    EntityViewMode::create(['id' => 'node.graphql', 'targetEntityType' => 'node'])->save();

    entity_get_display('node', 'test', 'graphql')
      ->setComponent('body', $options)
      ->setComponent('field_text', $options)
      ->setComponent('field_boolean', $options)
      ->setComponent('field_link', $options)
      ->setComponent('field_integer', $options)
      ->setComponent('field_float', $options)
      ->setComponent('field_decimal', $options)
      ->setComponent('field_datetime', $options)
      ->setComponent('field_timestamp', $options)
      ->setComponent('field_email', $options)
      ->setComponent('field_string', $options)
      ->setComponent('field_reference', $options)
      ->setComponent('field_file', $options)
      ->setComponent('field_image', $options)
      ->save();

    $this->container->get('config.factory')->getEditable('graphql_content.schema')
      ->set('types', [
        'node' => [
          'exposed' => TRUE,
          'bundles' => [
            'test' => [
              'exposed' => TRUE,
              'view_mode' => 'node.graphql',
            ],
          ],
        ],
      ])->save();
  }

  /**
   * Test if the basic fields are available on the interface.
   *
   * @dataProvider nodeValuesProvider
   */
  public function testRawValues($actualFieldValues, $expectedFieldValues) {
    $values = [
      'title' => 'Test',
      'type' => 'test',
    ];
    $node = $this->createNode($values + $actualFieldValues);

    $result = $this->executeQueryFile('raw_field_values.gql', [
      'path' => '/node/' . $node->id(),
    ]);
    $resultNode = NestedArray::getValue($result, ['data', 'route', 'entity']);

    $this->assertEquals($expectedFieldValues, $resultNode, 'Correct raw node values are returned.');
  }

  /**
   * Data provider for testRawValues.
   *
   * @return array
   */
  public function nodeValuesProvider() {
    $fieldValues = [
      'body' => [
        'value' => 'test',
        'summary' => 'test summary',
      ],
      'field_text' => ['a', 'b', 'c'],
      'field_boolean' => [TRUE, FALSE],
      'field_link' => [
        ['title' => 'Internal link', 'uri' => 'internal:/node/1'],
        ['title' => 'External link', 'uri' => 'http://drupal.org'],
      ],
      'field_integer' => [10, -5],
      'field_float' => [3.14145, -8.8],
      'field_decimal' => [10.5, -17.22],
      'field_datetime' => ['2017-01-01', '1900-01-01'],
      'field_timestamp' => [0, 300],
      'field_email' => ['test@test.com'],
      'field_string' => ['test', '123'],
      'field_reference' => [
        ['target_id' => 1],
        ['target_id' => 1],
      ],
      'field_file' => [
        [
          'target_id' => 1,
          'display' => 0,
          'description' => 'description test 1',
        ],
        [
          'target_id' => 2,
          'display' => 1,
          'description' => 'description test 2',
        ],
      ],
      'field_image' => [
        [
          'target_id' => 1,
          'alt' => 'alt test 1',
          'title' => 'title test 1',
          'width' => 100,
          'height' => 50,
        ],
        [
          'target_id' => 2,
          'alt' => 'alt test 2',
          'title' => 'title test 2',
          'width' => 200,
          'height' => 100,
        ],
      ],
    ];

    $expected = [
      'body' => [
        'value' => 'test',
        'summary' => 'test summary',
      ],
      'fieldText' => [
        ['value' => 'a'],
        ['value' => 'b'],
        ['value' => 'c'],
      ],
      'fieldBoolean' => [
        ['value' => TRUE],
        ['value' => FALSE],
      ],
      'fieldLink' => [
        ['title' => 'Internal link', 'uri' => 'internal:/node/1'],
        ['title' => 'External link', 'uri' => 'http://drupal.org'],
      ],
      'fieldInteger' => [
        ['value' => 10],
        ['value' => -5],
      ],
      'fieldFloat' => [
        ['value' => 3.14145],
        ['value' => -8.8],
      ],
      'fieldDecimal' => [
        ['value' => 10.5],
        ['value' => -17.22],
      ],
      'fieldDatetime' => [
        ['value' => '2017-01-01'],
        ['value' => '1900-01-01'],
      ],
      'fieldTimestamp' => [
        ['value' => 0],
        ['value' => 300],
      ],
      'fieldEmail' => [
        ['value' => 'test@test.com'],
      ],
      'fieldString' => [
        ['value' => 'test'],
        ['value' => '123'],
      ],
      'fieldReference' => [
        ['targetId' => 1],
        ['targetId' => 1],
      ],
      'fieldFile' => [
        [
          'targetId' => 1,
          'display' => 0,
          'description' => 'description test 1',
        ],
        [
          'targetId' => 2,
          'display' => 1,
          'description' => 'description test 2',
        ],
      ],
      'fieldImage' => [
        [
          'targetId' => 1,
          'alt' => 'alt test 1',
          'title' => 'title test 1',
          'width' => 100,
          'height' => 50,
        ],
        [
          'targetId' => 2,
          'alt' => 'alt test 2',
          'title' => 'title test 2',
          'width' => 200,
          'height' => 100,
        ],
      ],
    ];

    return [
      [$fieldValues, $expected],
    ];
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
