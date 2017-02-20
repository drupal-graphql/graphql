<?php

namespace Drupal\Tests\graphql\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\user\Entity\User;

/**
 * Class UuidTest tests the UUID loader.
 *
 * @group GraphQL
 */
class UuidTest extends KernelTestBase {

  const MODULE='graphql';

  public static $modules = [
    'field',
    'graphql',
    'node',
    'system',
    'text',
    'user',
  ];

  protected function setUp() {
    parent::setUp();
    $this->installEntitySchema('node');
    $this->installEntitySchema('user');
    $this->installSchema('user', 'users_data');
    $this->installSchema(static::MODULE, 'graphql_uuid');

    $this->installConfig(['field', 'node', 'user']);
  }

  public function testLoader() {
    $count = 3;
    $uuids = [];
    for ($i = 0 ; $i < $count ; $i++) {
      $account = User::create(['uid' => $i, 'name' => "user$i"]);
      $account->save();
      $uuids[$account->uuid()] = $account;
    }

    /** @var \Drupal\graphql\Utility\UuidHelper $uuid */
    $helper = $this->container->get('graphql.uuid_helper');
    $entities = $helper->loadEntitiesByUuid(array_keys($uuids));
    $this->assertEquals($count, count($entities), "Load returns as many entities as were created.");
    /** @var \Drupal\Core\Entity\EntityInterface $entity */
    foreach ($entities as $uuid => $entity) {
      $this->assertEquals('user', $entity->getEntityTypeId(), 'Entity is of the expected type');
      $this->assertEquals($entity->uuid(), $uuids[$uuid]->uuid());
    }

    /** @var \Drupal\user\Entity\User $user1 */
    reset($uuids);
    $user1 = next($uuids);
    $user1bis = $helper->loadEntityByUuid($user1->uuid());
    $this->assertInstanceOf('Drupal\user\Entity\User', $user1bis);
    $this->assertEquals($user1->id(), $user1bis->id());
    $this->assertEquals($user1->uuid(), $user1bis->uuid());

    $user1->delete();
    $user1bis = $helper->loadEntityByUuid($user1->uuid());
    $this->assertEmpty($user1bis, 'No user is loaded for the uuid of a deleted user.');

    $entities = $helper->loadEntitiesByUuid(array_keys($uuids));
    $this->assertEquals($count - 1, count($entities), 'The correct number of users is found');
    foreach ($entities as $uuid => $entity) {
      $this->assertEquals($entity->uuid(), $uuids[$uuid]->uuid());
    }

    foreach ($uuids as $account) {
      $account->delete();
    }

    $entities = $helper->loadEntitiesByUuid(array_keys($uuids));
    $this->assertEquals(0, count($entities), "Load no longer returns anything");
  }

}
