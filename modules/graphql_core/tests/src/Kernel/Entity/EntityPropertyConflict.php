<?php

namespace Drupal\Tests\graphql_core\Kernel\Entity;

use Drupal\Tests\graphql_core\Kernel\GraphQLContentTestBase;

/**
 * Test entity property naming conflict resolution.
 *
 * @group graphql_core
 */
class EntityPropertyConflict extends GraphQLContentTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'comment',
  ];

  /**
   * Check proper behavior in case of a naming conflict.
   */
  public function testNamingConflict() {
    $this->assertGraphQLFields([
      ['Comment', 'entityId', 'String'],
      ['Comment', 'entityIdOfComment', 'FieldCommentEntityId'],
    ]);
  }

}
