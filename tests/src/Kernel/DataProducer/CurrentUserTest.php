<?php

declare(strict_types=1);

namespace Drupal\Tests\graphql\Kernel\DataProducer;

use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Drupal\Tests\graphql\TestInvocationCounter;
use Drupal\Tests\user\Traits\UserCreationTrait;
use Drupal\user\Entity\User;

/**
 * Tests the current_user data producer.
 *
 * @coversDefaultClass \Drupal\graphql\Plugin\GraphQL\DataProducer\User\CurrentUser
 * @group graphql
 */
class CurrentUserTest extends GraphQLTestBase {

  use UserCreationTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['graphql_dataproducers_test'];

  /**
   * The GraphQL schema for this test.
   */
  protected const SCHEMA = <<<GQL
    type Query {
      currentUser: User
    }
    type User {
      id: Int!
    }
  GQL;

  /**
   * A GraphQL query to get the current user ID.
   */
  protected const QUERY = <<<GQL
    query {
      currentUser {
        id
      }
    }
  GQL;

  /**
   * Test users.
   *
   * @var array<\Drupal\user\UserInterface>
   */
  protected array $users;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->setUpSchema(self::SCHEMA);

    // Create two test users.
    $this->users = [
      $this->createUser(),
      $this->createUser(),
    ];

    // Log out initially.
    $this->container->get('current_user')->setAccount(User::getAnonymousUser());
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\User\CurrentUser::resolve
   */
  public function testCurrentUser(): void {
    $this->mockResolver('Query', 'currentUser',
      $this->builder->produce('current_user')
    );

    $counter = new TestInvocationCounter();

    $this->mockResolver('User', 'id',
      $this->builder->produce('test_user_id_counting')
        ->map('user', $this->builder->fromParent())
        ->map('counter', $this->builder->fromValue($counter))
    );

    // Initially no user is logged in. We expect the anonymous user (ID 0). The
    // result should be cached, so only the first call should trigger a query.
    $this->assertCurrentUser(0);
    $this->assertEquals(1, $counter->getCount(), 'The user ID was queried for the anonymous user.');
    $this->assertCurrentUser(0);
    $this->assertEquals(1, $counter->getCount(), 'When requesting the anonymous user a second time, the cached result was used.');

    // Log in as the first user.
    $this->container->get('current_user')->setAccount($this->users[0]);
    $this->assertCurrentUser((int) $this->users[0]->id());
    $this->assertEquals(2, $counter->getCount(), 'The user ID was queried for the first user.');
    $this->assertCurrentUser((int) $this->users[0]->id());
    $this->assertEquals(2, $counter->getCount(), 'When requesting the first user a second time, the cached result was used.');

    // Log in as the second user.
    $this->container->get('current_user')->setAccount($this->users[1]);
    $this->assertCurrentUser((int) $this->users[1]->id());
    $this->assertEquals(3, $counter->getCount(), 'The user ID was queried for the second user.');
    $this->assertCurrentUser((int) $this->users[1]->id());
    $this->assertEquals(3, $counter->getCount(), 'When requesting the second user a second time, the cached result was used.');

    // Make a change to the second user. This should invalidate the cache.
    $this->users[1]->setEmail('test@example.com')->save();
    $this->assertCurrentUser((int) $this->users[1]->id());
    $this->assertEquals(4, $counter->getCount(), 'After modifying the second user, the user ID was queried again.');
    $this->assertCurrentUser((int) $this->users[1]->id());
    $this->assertEquals(4, $counter->getCount(), 'When requesting the second user a second time after modification, the cached result was used.');

    // Log out. We already have the anonymous user cached so we should get
    // cached results.
    $this->container->get('current_user')->setAccount(User::getAnonymousUser());
    $this->assertCurrentUser(0);
    $this->assertEquals(4, $counter->getCount(), 'When requesting the anonymous user again, the cached result was used.');
  }

  /**
   * Asserts that the queries current user ID matches the expected value.
   *
   * @param int $expectedId
   *   The expected user ID.
   */
  protected function assertCurrentUser(int $expectedId): void {
    $metadata = $this->defaultCacheMetaData();
    $metadata
      ->addCacheContexts(['user'])
      ->addCacheTags(['user:' . $expectedId]);

    $this->assertResults(self::QUERY, [], [
      'currentUser' => ['id' => $expectedId],
    ], $metadata);
  }

}
