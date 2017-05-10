<?php

namespace Drupal\Tests\graphql_boolean\Kernel;

use Drupal\Core\Entity\Entity\EntityViewDisplay;
use Drupal\Core\Entity\Entity\EntityViewMode;
use Drupal\entity_test\Entity\EntityTest;
use Drupal\entity_test\Entity\EntityTestBundle;
use Drupal\entity_test\Entity\EntityTestWithBundle;
use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\graphql_core\Traits\GraphQLFileTestTrait;
use Drupal\user\Entity\Role;

/**
 * Test boolean graphql fields.
 *
 * @group graphql_boolean
 */
class BooleanFieldTest extends KernelTestBase {
  use GraphQLFileTestTrait;

  public static $modules = [
    'system',
    'path',
    'field',
    'text',
    'entity_test',
    'user',
    'graphql',
    'graphql_core',
    'graphql_content',
    'graphql_boolean',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $this->installConfig('user');
    $this->installEntitySchema('user');
    $this->installEntitySchema('entity_test_with_bundle');

    Role::load('anonymous')
      ->grantPermission('view test entity')
      ->save();

    EntityTestBundle::create([
      'id' => 'graphql',
      'label' => 'GraphQL',
    ])->save();

    FieldStorageConfig::create([
      'field_name' => 'flagged',
      'type' => 'boolean',
      'entity_type' => 'entity_test_with_bundle',
    ])->save();

    FieldConfig::create([
      'field_name' => 'flagged',
      'entity_type' => 'entity_test_with_bundle',
      'bundle' => 'graphql',
      'label' => 'Flagged',
    ])->save();

    EntityViewMode::create([
      'targetEntityType' => 'entity_test_with_bundle',
      'id' => "entity_test_with_bundle.graphql",
    ])->save();

    EntityViewDisplay::create([
      'targetEntityType' => 'entity_test_with_bundle',
      'bundle' => 'graphql',
      'mode' => 'graphql',
      'status' => TRUE,
    ])->setComponent('flagged', ['type' => 'boolean'])->save();
  }

  /**
   * Test correct boolean field behavior.
   */
  public function testBooleanField() {
    $true = EntityTestWithBundle::create([
      'type' => 'graphql',
      'name' => 'Boolean test: true',
      'flagged' => TRUE,
    ]);
    $true->save();

    $false = EntityTestWithBundle::create([
      'type' => 'graphql',
      'name' => 'Boolean test: false',
      'flagged' => FALSE,
    ]);
    $false->save();

    $result = $this->executeQueryFile('boolean.gql', [
      'true_path' => '/entity_test_with_bundle/' . $true->id(),
      'false_path' => '/entity_test_with_bundle/' . $false->id(),
    ]);

    $this->assertEquals(TRUE, $result['data']['isTrue']['entity']['flagged'], 'Boolean TRUE is correct.');
    $this->assertEquals(FALSE, $result['data']['isFalse']['entity']['flagged'], 'Boolean FALSE is correct.');
  }

}
