<?php

namespace Drupal\Tests\graphql\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\graphql\Traits\ByPassAccessTrait;
use Drupal\Tests\graphql\Traits\QueryTrait;
use Drupal\Tests\graphql\Traits\SchemaProphecyTrait;
use Prophecy\Argument;
use Youshido\GraphQL\Schema\Schema;
use Youshido\GraphQL\Type\Scalar\StringType;

/**
 * Verify that all queries declare the user.permissions cache context.
 *
 * This is imperative to ensure that authorized queries are not cached
 * and served to unauthorized users.
 *
 * @group graphql
 */
class UserPermissionsContextTest extends KernelTestBase {
  use QueryTrait;
  use ByPassAccessTrait;
  use SchemaProphecyTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['graphql'];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();
    $root = $this->prophesizeField('root', new StringType());
    $root->resolve(Argument::cetera())->willReturn('test');

    $schema = $this->createSchema($this->container, $root->reveal());
    $this->injectSchema($schema);
  }

  public function testUserPermissionsContext() {
    $result = $this->query('query { root }');
    $this->assertContains('user.permissions', $result->getCacheableMetadata()->getCacheContexts());
  }
}