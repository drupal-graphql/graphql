<?php

namespace Drupal\Tests\graphql_core\Kernel\Entity;

use Drupal\Component\Utility\NestedArray;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\file\Entity\File;
use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\Tests\graphql\Kernel\GraphQLFileTestBase;
use Drupal\user\Entity\Role;

/**
 * Test basic entity fields.
 *
 * @group graphql_core
 */
class EntityFieldValueTest extends GraphQLFileTestBase {
  use ContentTypeCreationTrait;
  use NodeCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'graphql_core',
    'node',
    'field',
    'filter',
    'text',
    'graphql_core',
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
    $this->installSchema('file', ['file_usage']);

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

    // File 1
    file_put_contents('public://example.txt', $this->randomMachineName());
    File::create([
      'uri' => 'public://example.txt',
    ])->save();

    // File 2
    file_put_contents('public://example.png', $this->randomMachineName());
    File::create([
      'uri' => 'public://example.png',
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
      'nid' => 1,
      'vid' => 1,
      'langcode' => [
        'value' => 'en',
      ],
      'type' => [
        'targetId' => 'test',
      ],
      'title' => 'Test',
      'status' => 1,
      'promote' => 1,
      'sticky' => 0,
      'revisionTranslationAffected' => 1,
      'body' => [
        'value' => 'test',
        'summary' => 'test summary',
        'summaryProcessed' => "<p>test summary</p>\n",
        'processed' => "<p>test</p>\n",
        'format' => null,
      ],
      'fieldText' => [
        ['value' => 'a'],
        ['value' => 'b'],
        ['value' => 'c'],
      ],
      'fieldBoolean' => [
        TRUE,
        FALSE,
      ],
      'fieldLink' => [
        ['title' => 'Internal link', 'uri' => 'internal:/node/1'],
        ['title' => 'External link', 'uri' => 'http://drupal.org'],
      ],
      'fieldInteger' => [
        10,
        -5,
      ],
      'fieldFloat' => [
        3.14145,
        -8.8,
      ],
      'fieldDecimal' => [
        10.5,
        -17.22,
      ],
      'fieldDatetime' => [
        ['value' => '2017-01-01'],
        ['value' => '1900-01-01'],
      ],
      'fieldTimestamp' => [
        0,
        300,
      ],
      'fieldEmail' => ['test@test.com'],
      'fieldString' => [
        'test',
        '123',
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
