<?php

namespace Drupal\Tests\graphql_core\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Tests\graphql_core\Traits\RevisionsTestTrait;
use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\simpletest\UserCreationTrait;

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
  }

  /**
   * {@inheritdoc}
   */
  protected function defaultCacheTags() {
    // graphql_core adds the node body field to the schema, which means
    // it's always part of the result cache tags, even if it has not been
    // queried.
    //
    // https://github.com/drupal-graphql/graphql/issues/500
    return array_merge(parent::defaultCacheTags(), [
      'config:field.storage.node.body',
    ]);
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
  protected function addField($type, $fieldName, $label = 'Label', $bundle = 'test') {
    FieldStorageConfig::create([
      'field_name' => $fieldName,
      'entity_type' => 'node',
      'type' => $type,
      'cardinality' => -1,
    ])->save();

    FieldConfig::create([
      'entity_type' => 'node',
      'bundle' => $bundle,
      'field_name' => $fieldName,
      'label' => $label,
    ])->save();
  }

}
