<?php

namespace Drupal\Tests\graphql_core\Kernel\Entity;

use Drupal\field\Entity\FieldConfig;
use Drupal\field\Entity\FieldStorageConfig;
use Drupal\Tests\graphql_core\Kernel\GraphQLContentTestBase;

/**
 * Tests for bundle-less entities.
 *
 * Test edge cases of entities without bundles (e.g. the user entity).
 *
 * @group graphql_core
 */
class BundleLessEntityTest extends GraphQLContentTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    FieldStorageConfig::create([
      'field_name' => 'field_test',
      'entity_type' => 'user',
      'type' => 'boolean',
      'cardinality' => 1,
    ])->save();

    FieldConfig::create([
      'entity_type' => 'user',
      'bundle' => 'user',
      'field_name' => 'field_test',
      'label' => 'Test',
    ])->save();
  }

  /**
   * Test if a field is available on the user type.
   *
   * Regression test for: https://github.com/drupal-graphql/graphql/issues/560
   */
  public function testConfiguredField() {
    $this->assertGraphQLFields([
      ['User', 'fieldTest', 'Boolean'],
    ]);
  }

}
