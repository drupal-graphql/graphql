<?php

namespace Drupal\Tests\graphql\Kernel;

use Drupal\graphql\GraphQL\Validator;
use Drupal\graphql\GraphQL\ValidatorInterface;

/**
 * Tests that the GraphQL validator behaves correctly.
 *
 * @coversDefaultClass \Drupal\graphql\GraphQL\Validator
 *
 * @group graphql
 */
class ValidatorTest extends GraphQLTestBase {

  /**
   * The validator under test.
   *
   * @var \Drupal\graphql\GraphQL\ValidatorInterface
   */
  protected ValidatorInterface $validator;

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->validator = new Validator($this->schemaPluginManager);
  }

  /**
   * @covers ::getMissingResolvers
   */
  public function testGetMissingResolversCatchesMissingFieldsOnTypes() : void {
    $schema = <<<GQL
      type Query {
        me: String
      }
GQL;
    $this->setUpSchema($schema);

    $missing_resolvers = $this->validator->getMissingResolvers($this->server);

    self::assertEquals(
      ['Query' => ['me']],
      $missing_resolvers
    );
  }

  /**
   * @covers ::getMissingResolvers
   *
   * Interfaces are ignored because the implementing types are used to check
   * whether a resolver is present.
   */
  public function testGetMissingResolversIgnoresMissingFieldsOnInterfaces() : void {
    $schema = <<<GQL
      type Query {
        me: Actor
      }

      interface Actor {
        name: String!
      }

      type User implements Actor {
        name: String!
      }
GQL;
    $this->setUpSchema($schema);
    $this->mockResolver('Query', 'me', $this->builder->fromValue('Test User'));
    $this->mockResolver('User', 'name', $this->builder->fromParent());

    $missing_resolvers = $this->validator->getMissingResolvers($this->server);
    self::assertEquals([], $missing_resolvers);
  }

  /**
   * @covers ::getMissingResolvers
   */
  public function testGetMissingResolversCanIgnoreTypes() : void {
    $schema = <<<GQL
      type Query {
        me: User
      }

      type User {
        name: String!
      }
GQL;
    $this->setUpSchema($schema);
    $this->mockResolver('Query', 'me', $this->builder->fromValue('Test User'));

    $missing_resolvers = $this->validator->getMissingResolvers($this->server, ['User']);

    self::assertEquals([], $missing_resolvers);
  }

  /**
   * @covers ::getOrphanedResolvers
   */
  public function testGetOrphanedResolversDetectsUnfieldableObjectResolvers() : void {
    $schema = <<<GQL
      type Query {
        me: Actor
      }

      union Actor = User

      type User {
        name: String!
      }
GQL;
    $this->setUpSchema($schema);
    $this->mockResolver('Actor', 'name', $this->builder->fromValue('Never used'));

    $orphaned_resolvers = $this->validator->getOrphanedResolvers($this->server);
    self::assertEquals(['Actor' => ['name']], $orphaned_resolvers);
  }

  /**
   * @covers ::getOrphanedResolvers
   */
  public function testGetOrphanedResolversDetectsNonExistentResolvers() : void {
    $schema = <<<GQL
      type Query {
        me: String!
      }

GQL;
    $this->setUpSchema($schema);
    $this->mockResolver('Query', 'me', $this->builder->fromValue('Test User'));
    $this->mockResolver('User', 'name', $this->builder->fromValue('Orphaned Name'));

    $orphaned_resolvers = $this->validator->getOrphanedResolvers($this->server);
    self::assertEquals(['User' => ['name']], $orphaned_resolvers);
  }

  /**
   * @covers ::getOrphanedResolvers
   */
  public function testGetOrphanedResolversDetectsOrphanedObjectFieldResolvers() : void {
    $schema = <<<GQL
      type Query {
        me: User
      }

      type User {
        name: String!
      }
GQL;
    $this->setUpSchema($schema);
    $this->mockResolver('Query', 'me', $this->builder->fromValue('Test User'));
    $this->mockResolver('User', 'name', $this->builder->fromParent());
    $this->mockResolver('User', 'birthday', $this->builder->fromValue('01-02-2021'));

    $orphaned_resolvers = $this->validator->getOrphanedResolvers($this->server);
    self::assertEquals(['User' => ['birthday']], $orphaned_resolvers);
  }

  /**
   * @covers ::getOrphanedResolvers
   */
  public function testGetOrphanedResolversDetectsOrphanedInterfaceFieldResolvers() : void {
    $schema = <<<GQL
      type Query {
        me: Actor
      }

      interface Actor {
        name: String!
      }

      type User implements Actor {
        name: String!
      }
GQL;
    $this->setUpSchema($schema);
    $this->mockResolver('Query', 'me', $this->builder->fromValue('Test User'));
    $this->mockResolver('Actor', 'name', $this->builder->fromParent());
    $this->mockResolver('Actor', 'birthday', $this->builder->fromValue('01-02-2021'));

    $orphaned_resolvers = $this->validator->getOrphanedResolvers($this->server);
    self::assertEquals(['Actor' => ['birthday']], $orphaned_resolvers);
  }

  /**
   * @covers ::getOrphanedResolvers
   */
  public function testGetOrphanedResolversDetectsOrphanedInputObjectFieldResolvers() : void {
    $schema = <<<GQL
      type Mutation {
        createFakeObject(input: FakeInput!): User
      }

      type User {
        name: String!
      }

      input FakeInput {
        message: String!
      }

GQL;
    $this->setUpSchema($schema);
    $this->mockResolver('FakeInput', 'removed_field', $this->builder->fromValue('Test User'));

    $orphaned_resolvers = $this->validator->getOrphanedResolvers($this->server);
    self::assertEquals(['FakeInput' => ['removed_field']], $orphaned_resolvers);
  }

  /**
   * @covers ::getOrphanedResolvers
   */
  public function testGetOrphanedResolversDetectsCanIgnoreTypes() : void {
    $schema = <<<GQL
      type Query {
        me: Actor
      }

      union Actor = User

      type User {
        name: String!
      }
GQL;
    $this->setUpSchema($schema);
    $this->mockResolver('Actor', 'name', $this->builder->fromValue('Never used'));

    $orphaned_resolvers = $this->validator->getOrphanedResolvers($this->server, ['Actor']);
    self::assertEquals([], $orphaned_resolvers);
  }

}
