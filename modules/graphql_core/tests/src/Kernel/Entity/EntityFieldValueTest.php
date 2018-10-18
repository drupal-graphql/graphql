<?php

namespace Drupal\Tests\graphql_core\Kernel\Entity;

use Drupal\file\Entity\File;
use Drupal\graphql\Utility\StringHelper;
use Drupal\node\Entity\Node;
use Drupal\Tests\graphql_core\Kernel\GraphQLContentTestBase;
use GraphQL\Server\OperationParams;

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
  }

  /**
   * Test boolean fields.
   */
  public function testBoolean() {
    $this->addField('boolean', "field_boolean", FALSE);

    $this->mockNode([
      'field_boolean' => TRUE,
    ]);

    $this->assertGraphQLFields([
      ['NodeTest', 'fieldBoolean', 'Boolean'],
    ]);

    $query = <<<GQL
query {
  node {
    fieldBoolean
  }
}
GQL;

    $metadata = $this->defaultCacheMetaData();

    $this->assertResults($query, [], [
      'node' => [
        'fieldBoolean' => TRUE,
      ],
    ], $metadata);
  }

  /**
   * Test a simple text field.
   */
  public function testText() {
    $this->addField('text', "field_text", FALSE);
    $this->mockNode([
      'field_text' => [
        'value' => 'Foo',
      ],
    ]);

    $this->assertGraphQLFields([
      ['NodeTest', 'fieldText', 'FieldNodeTestFieldText'],
      ['FieldNodeTestFieldText', 'value', 'String'],
      ['FieldNodeTestFieldText', 'processed', 'String'],
      ['FieldNodeTestFieldText', 'format', 'String'],
    ]);

    $query = <<<GQL
query {
  node {
    fieldText {
      value
      processed
      format
    }
  }
}
GQL;

    $metadata = $this->defaultCacheMetaData();

    $this->assertResults($query, [], [
      'node' => [
        'fieldText' => [
          'value' => 'Foo',
          'processed' => "<p>Foo</p>\n",
          'format' => null,
        ],
      ],
    ], $metadata);

  }

  /**
   * Test filtered text fields.
   */
  public function testFilteredText() {
    $this->mockNode([
      'body' => [
        'value' => 'http://www.drupal.org',
        'format' => 'plain_text',
      ],
    ]);

    $this->assertGraphQLFields([
      ['NodeTest', 'body', 'FieldNodeTestBody'],
      ['FieldNodeTestBody', 'format', 'String'],
      ['FieldNodeTestBody', 'value', 'String'],
      ['FieldNodeTestBody', 'processed', 'String'],
      ['FieldNodeTestBody', 'summary', 'String'],
      ['FieldNodeTestBody', 'summaryProcessed', 'String'],
    ]);

    $query = <<<GQL
query {
  node {
    body {
      value
      processed
      summary
      summaryProcessed
    }
  }
}
GQL;

    $metadata = $this->defaultCacheMetaData();

    $this->assertResults($query, [], [
      'node' => [
        'body' => [
          'value' => 'http://www.drupal.org',
          'processed' => "<p><a href=\"http://www.drupal.org\">http://www.drupal.org</a></p>\n",
          'summary' => null,
          'summaryProcessed' => '',
        ],
      ],
    ], $metadata);
  }

  /**
   * Verify that fields are assigned correctly among bundles.
   */
  public function testFieldAssignment() {
    $this->createContentType(['type' => 'a']);
    $this->createContentType(['type' => 'b']);
    $this->addField('boolean', 'field_a', FALSE, 'A', 'a');
    $this->addField('boolean', 'field_b', FALSE, 'B', 'b');

    // Verify that the fields for a given bundle are there.
    $this->assertGraphQLFields([
      ['NodeA', 'fieldA', 'Boolean'],
      ['NodeB', 'fieldB', 'Boolean'],
    ]);

    // Verify that the fields of another bundle are *not* there.
    $this->assertGraphQLFields([
      ['NodeA', 'fieldB', 'Boolean'],
      ['NodeB', 'fieldA', 'Boolean'],
    ], TRUE);
  }

  /**
   * Test if the basic fields are available on the interface.
   *
   * @dataProvider nodeValuesProvider
   *
   * @param array $actualFieldValues
   * @param array $expectedFieldValues
   */
  public function testRawValues($actualFieldValues, $expectedFieldValues) {
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
      'node:1',
      'user:0',
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
      'field_reference' =>  [
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
        'entity' => [
          'name' => '',
        ],
      ],
      'title' => 'Test',
      'status' => TRUE,
      'promote' => TRUE,
      'sticky' => FALSE,
      'revisionTranslationAffected' => TRUE,
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
          'display' => FALSE,
          'description' => 'description test 1',
          'entity' => [
//            'uri' => [
//              'value' => 'public://example.txt',
//            ],
          ],
        ],
        [
          'targetId' => 2,
          'display' => TRUE,
          'description' => 'description test 2',
          'entity' => [
//            'uri' => [
//              'value' => 'public://example.png',
//            ],
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
//            'uri' => [
//              'value' => 'public://example.txt',
//            ],
          ],
        ],
        [
          'targetId' => 2,
          'alt' => 'alt test 2',
          'title' => 'title test 2',
          'width' => 200,
          'height' => 100,
          'entity' => [
//            'uri' => [
//              'value' => 'public://example.png',
//            ],
          ],
        ],
      ],
    ];

    return [
      [$fieldValues, $expected],
    ];
  }

}
