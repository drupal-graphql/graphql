<?php

namespace Drupal\Tests\graphql_core\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\node\Entity\Node;
use Drupal\Tests\graphql_core\Traits\RevisionsTestTrait;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\Tests\user\Traits\UserCreationTrait;

/**
 * Base class for node based tests.
 */
class GraphQLContentTestBase extends GraphQLCoreTestBase {
  use ContentTypeCreationTrait;
  use UserCreationTrait;
  use NodeCreationTrait;
  use RevisionsTestTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'content_translation',
    'node',
    'field',
    'filter',
    'text',
  ];

  /**
   * {@inheritdoc}
   *
   * Add the 'access content' permission to the mocked account.
   */
  protected function userPermissions() {
    $perms = parent::userPermissions();
    $perms[] = 'access content';
    $perms[] = 'access user profiles';
    return $perms;
  }

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->installConfig(['node', 'filter', 'text']);
    $this->installEntitySchema('node');

    $this->installSchema('node', 'node_access');
    $this->installSchema('system', 'sequences');

    $this->createContentType([
      'type' => 'test',
    ]);

    $this->container->get('content_translation.manager')
      ->setEnabled('node', 'test', TRUE);
  }

  /**
   * Mock a field that emits a test node.
   *
   * ```
   * query {
   *   node {
   *     title
   *   }
   * }
   * ```
   *
   * @param mixed $values
   *   Additional node values.
   * @param string $title
   *   An optional title. Will default to "Test".
   */
  protected function mockNode($values, $title = 'Test') {
    $this->mockField('node', [
      'name' => 'node',
      'type' => 'entity:node:test',
    ], Node::create([
      'title' => $title,
      'type' => 'test',
    ] + $values));
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
  protected function addField($type, $fieldName, $multi = TRUE, $label = 'Label', $bundle = 'test') {
    FieldStorageConfig::create([
      'field_name' => $fieldName,
      'entity_type' => 'node',
      'type' => $type,
      'cardinality' => $multi ? -1 : 1,
    ])->save();

    FieldConfig::create([
      'entity_type' => 'node',
      'bundle' => $bundle,
      'field_name' => $fieldName,
      'label' => $label,
    ])->save();
  }

}
