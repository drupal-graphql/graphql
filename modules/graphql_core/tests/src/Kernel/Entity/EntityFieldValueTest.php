<?php

namespace Drupal\Tests\graphql_core\Kernel\Entity;

use Drupal\file\Entity\File;
use Drupal\Tests\graphql_core\Kernel\GraphQLContentTestBase;

/**
 * Test basic entity fields.
 *
 * @group graphql_core
 */
class EntityFieldValueTest extends GraphQLContentTestBase {

  /**
   * @var File
   */
  protected $testFile;

  /**
   * @var File
   */
  protected $testImage;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
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

    $this->installEntitySchema('file');
    $this->installSchema('file', ['file_usage']);
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

    // File 1
    file_put_contents('public://example.txt', $this->randomMachineName());
    $this->testFile = File::create([
      'uri' => 'public://example.txt',
    ]);
    $this->testFile->save();

    // File 2
    file_put_contents('public://example.png', $this->randomMachineName());
    $this->testImage = File::create([
      'uri' => 'public://example.png',
    ]);
    $this->testImage->save();
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

    // Workaround for public file urls.
    $expectedFieldValues['fieldFile'][0]['entity']['url'] = file_create_url($this->testFile->getFileUri());
    $expectedFieldValues['fieldFile'][1]['entity']['url'] = file_create_url($this->testImage->getFileUri());
    $expectedFieldValues['fieldImage'][0]['entity']['url'] = file_create_url($this->testFile->getFileUri());
    $expectedFieldValues['fieldImage'][1]['entity']['url'] = file_create_url($this->testImage->getFileUri());

    $metadata = $this->defaultCacheMetaData();

    $metadata->addCacheTags([
      'entity_bundles',
      'entity_field_info',
      'entity_types',
      'node:1',
      'config:field.storage.node.body',
      'config:field.storage.node.field_text',
      'config:field.storage.node.field_boolean',
      'config:field.storage.node.field_datetime',
      'config:field.storage.node.field_decimal',
      'config:field.storage.node.field_email',
      'config:field.storage.node.field_float',
      'config:field.storage.node.field_file',
      'config:field.storage.node.field_image',
      'config:field.storage.node.field_integer',
      'config:field.storage.node.field_link',
      'config:field.storage.node.field_string',
      'config:field.storage.node.field_timestamp',
      'config:field.storage.node.field_reference',
      'entity_bundles',
      'entity_field_info',
      'entity_types',
      'file:1',
      'file:2',
    ]);

    $this->assertResults($this->getQueryFromFile('raw_field_values.gql'), [
      'path' => '/node/' . $node->id(),
    ], [
      'route' => ['entity' => $expectedFieldValues],
    ], $metadata);
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
        [
          'title' => 'Internal link',
          'uri' => 'internal:/node/1',
          'options' => ['attributes' => ['_target' => 'blank']],
        ], [
          'title' => 'External link',
          'uri' => 'http://drupal.org',
          'options' => ['attributes' => ['_target' => 'blank']],
        ],
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
      'uid' => [
        'targetId' => 0,
        'entity' => NULL,
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
        'format' => NULL,
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
        ['title' => 'Internal link', 'uri' => 'internal:/node/1', 'target' => 'blank', 'url' => ['internal' => '/node/1']],
        ['title' => 'External link', 'uri' => 'http://drupal.org', 'target' => 'blank', 'url' => ['external' => 'http://drupal.org']],
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
        [
          'targetId' => 1,
          'entity' => [
            'title' => 'Test',
            'fieldReference' => [
              [
                'targetId' => 1,
                'entity' => [
                  'title' => 'Test',
                ],
              ],
            ],
          ],
        ],
      ],
      'fieldFile' => [
        [
          'targetId' => 1,
          'display' => 0,
          'description' => 'description test 1',
          'entity' => [
            'uri' => 'public://example.txt',
          ],
        ],
        [
          'targetId' => 2,
          'display' => 1,
          'description' => 'description test 2',
          'entity' => [
            'uri' => 'public://example.png',
          ],
        ],
      ],
      'fieldImage' => [
        [
          'targetId' => 1,
          'alt' => 'alt test 1',
          'title' => 'title test 1',
          'width' => 100,
          'height' => 50,
          'entity' => [
            'uri' => 'public://example.txt',
          ],
        ],
        [
          'targetId' => 2,
          'alt' => 'alt test 2',
          'title' => 'title test 2',
          'width' => 200,
          'height' => 100,
          'entity' => [
            'uri' => 'public://example.png',
          ],
        ],
      ],
    ];

    return [
      [$fieldValues, $expected],
    ];
  }

}
