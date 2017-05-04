<?php

namespace Drupal\Tests\graphql_content\Kernel;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\Tests\graphql_content\Kernel\EntityRenderedFieldsTest;
use Drupal\Tests\graphql_core\Kernel\GraphQLFileTestBase;
use Drupal\user\Entity\Role;

/**
 * Test basic entity fields.
 *
 * @group graphql_content
 */
class EntityOverrideFieldsTest extends EntityRenderedFieldsTest {

  /**
   * {@inheritdoc}
   */
  public static $modules = ['graphql_content_override_test'];

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

    $this->assertEquals('<P>TEST</P>', $node['body'], 'Body field is overridden.');
    $this->assertEquals(['<P>A</P>', '<P>B</P>', '<P>C</P>'], $node['fieldKeywords'], 'Multi value rendered field is overridden.');

    $this->assertEquals('This is a test.', $node['test'], 'Extra field is untouched.');
  }

}
