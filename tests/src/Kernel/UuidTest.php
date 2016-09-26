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
    $accounts = [];
    $uuids = [];
    for ($i = 0 ; $i < $count ; $i++) {
      $account = User::create(['uid' => $i, 'name' => "user$i"]);
      $account->save();
      $accounts[$account->id()] = $account;
      $uuids[$account->uuid()] = $account;
    }

    /** @var \Drupal\graphql\Utility\UuidHelper $uuid */
    $uuid = $this->container->get('graphql.uuid_helper');
    $entities = $uuid->loadEntitiesByUuid(array_keys($uuids));
    $this->assertEquals(1, count($entities), "Load only returns one entity type");
    $this->assertArrayHasKey('user', $entities, "Only users are found");
    $loadedAccounts = $entities['user'];
    $this->assertEquals($count, count($loadedAccounts), 'The correct number of users is found');
    foreach ($loadedAccounts as $uid => $loadedAccount) {
      $this->assertEquals($loadedAccount->uuid(), $accounts[$uid]->uuid());
    }

    /** @var \Drupal\user\Entity\User $user1 */
    $user1 = $accounts[1];
    $user1->delete();

    $entities = $uuid->loadEntitiesByUuid(array_keys($uuids));
    $this->assertEquals(1, count($entities), "Load only returns one entity type");
    $this->assertArrayHasKey('user', $entities, "Only users are found");
    $loadedAccounts = $entities['user'];
    $this->assertEquals($count - 1, count($loadedAccounts), 'The correct number of users is found');
    foreach ($loadedAccounts as $uid => $loadedAccount) {
      $this->assertEquals($loadedAccount->uuid(), $accounts[$uid]->uuid());
    }

    foreach ($accounts as $account) {
      $account->delete();
    }

    $entities = $uuid->loadEntitiesByUuid(array_keys($uuids));
    $this->assertEquals(0, count($entities), "Load no longer returns anything");
  }

}
