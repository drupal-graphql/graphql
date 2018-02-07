<?php

namespace Drupal\Tests\graphql_core\Kernel;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Drupal\Tests\graphql_core\Traits\RevisionsTestTrait;
use Drupal\Tests\node\Traits\ContentTypeCreationTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\Tests\user\Traits\UserCreationTrait;

/**
 * Base class for node based tests.
 */
class GraphQLContentTestBase extends GraphQLTestBase {
  use ContentTypeCreationTrait;
  use UserCreationTrait;
  use NodeCreationTrait;
  use RevisionsTestTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'graphql_core',
    'user',
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
    $this->installEntitySchema('user');

    $this->installSchema('node', 'node_access');
    $this->installSchema('system', 'sequences');

    $this->createContentType([
      'type' => 'test',
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
