<?php

declare(strict_types=1);

namespace Drupal\Tests\graphql\Kernel;

use GraphQL\Deferred;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests conditional and default value resolver builder behavior.
 *
 * @coversDefaultClass \Drupal\graphql\GraphQL\ResolverBuilder
 *
 * @group graphql
 */
#[Group('graphql')]
#[RunTestsInSeparateProcesses]
#[AllowMockObjectsWithoutExpectations]
class ResolverBuilderConditionTest extends ResolverBuilderTestBase {

  /**
   * @covers ::cond
   */
  public function testSingleCond(): void {
    $this->mockResolver('Query', 'me', $this->builder->cond([
      [
        $this->builder->fromValue(FALSE),
        $this->builder->fromValue('This should resolve into null.'),
      ],
    ]));

    $query = <<<GQL
      query {
        me
      }
GQL;

    $this->assertResults($query, [], ['me' => NULL]);
  }

  /**
   * @covers ::cond
   */
  public function testSimpleCond(): void {
    $this->mockResolver('Query', 'tree', $this->builder->fromValue([
      'name' => 'some tree',
      'id' => 5,
    ]));
    $this->mockResolver('Tree', 'name', $this->builder->cond([
      [
        $this->builder->fromValue(FALSE),
        $this->builder->fromValue('This should not be in the result.'),
      ],
      [
        $this->builder->fromValue(TRUE),
        $this->builder->fromValue('But this should.'),
      ],
      [
        $this->builder->fromValue(TRUE),
        $this->builder->fromValue('And this not, event though its true.'),
      ],
    ]));

    $query = <<<GQL
      query {
        tree {
          name
        }
      }
GQL;

    $this->assertResults($query, [], ['tree' => ['name' => 'But this should.']]);
  }

  /**
   * @covers ::cond
   */
  public function testDeferredCond(): void {
    $this->mockResolver('Query', 'tree', $this->builder->fromValue([
      'name' => 'some tree',
      'id' => 5,
    ]));
    $this->mockResolver('Tree', 'name', $this->builder->cond([
      [
        $this->builder->fromValue(FALSE),
        $this->builder->fromValue('This should not be in the result.'),
      ],
      [
        static function () {
          return new Deferred(static function () {
            return TRUE;
          });
        },
        $this->builder->fromValue('But this should.'),
      ],
      [
        $this->builder->fromValue(TRUE),
        $this->builder->fromValue('And this not, event though its true.'),
      ],
    ]));

    $query = <<<GQL
      query {
        tree {
          name
        }
      }
GQL;

    $this->assertResults($query, [], ['tree' => ['name' => 'But this should.']]);
  }

  /**
   * @covers ::cond
   */
  public function testParentCond(): void {
    $this->mockResolver('Query', 'tree', ['name' => 'some tree', 'id' => 5]);
    $this->mockResolver('Tree', 'name', $this->builder->cond([
      [
        $this->builder->fromValue(FALSE),
        $this->builder->fromValue('This should not be in the result.'),
      ],
      [
        $this->builder->fromParent(),
        $this->builder->fromValue('But this should.'),
      ],
      [
        $this->builder->fromValue(TRUE),
        $this->builder->fromValue('And this not, event though its true.'),
      ],
    ]));

    $query = <<<GQL
      query {
        tree {
          name
        }
      }
GQL;

    $this->assertResults($query, [], ['tree' => ['name' => 'But this should.']]);
  }

  /**
   * @covers ::defaultValue
   */
  public function testSimpleDefaultValue(): void {
    $this->mockResolver('Query', 'tree', ['name' => 'some tree', 'id' => 5]);
    $this->mockResolver('Tree', 'name', $this->builder->defaultValue(
      $this->builder->fromValue(NULL),
      $this->builder->fromValue('bar')
    ));

    $this->mockResolver('Tree', 'uri', $this->builder->defaultValue(
      $this->builder->fromValue('baz'),
      $this->builder->fromValue('bar')
    ));

    $query = <<<GQL
      query {
        tree(id: 1) {
          name
          uri
        }
      }
GQL;

    $this->assertResults($query, [], [
      'tree' => [
        'name' => 'bar',
        'uri' => 'baz',
      ],
    ]);
  }

  /**
   * Tests the composite default value resolver.
   */
  public function testCompositeDefaultValue(): void {

    $this->mockResolver('Query', 'tree', ['name' => 'some tree', 'id' => 5]);
    $this->mockResolver('Tree', 'name', $this->builder->defaultValue(
      $this->builder->compose(
        $this->builder->fromValue('baz'),
        $this->builder->fromValue(NULL)
      ),
      $this->builder->fromValue('bar')
    ));

    $this->mockResolver('Tree', 'uri', $this->builder->defaultValue(
      $this->builder->compose(
        $this->builder->fromValue(TRUE),
        $this->builder->fromValue('baz')
      ),
      $this->builder->fromValue('bar')
    ));

    $query = <<<GQL
      query {
        tree(id: 1) {
          name
          uri
        }
      }
GQL;

    $this->assertResults($query, [], [
      'tree' => [
        'name' => 'bar',
        'uri' => 'baz',
      ],
    ]);
  }

  /**
   * @covers ::defaultValue
   */
  public function testDeferredDefaultValue(): void {
    $this->mockResolver('Query', 'tree', ['name' => 'some tree', 'id' => 5]);
    $this->mockResolver('Tree', 'name', $this->builder->defaultValue(
      $this->builder->produce('test_deferred')->map('value', $this->builder->fromValue(NULL)),
      $this->builder->produce('test_deferred')->map('value', $this->builder->fromValue('bar'))
    ));

    $this->mockResolver('Tree', 'uri', $this->builder->defaultValue(
      $this->builder->produce('test_deferred')->map('value', $this->builder->fromValue('baz')),
      $this->builder->produce('test_deferred')->map('value', $this->builder->fromValue('bar'))
    ));

    $query = <<<GQL
      query {
        tree(id: 1) {
          name
          uri
        }
      }
GQL;

    $this->assertResults($query, [], [
      'tree' => [
        'name' => 'bar',
        'uri' => 'baz',
      ],
    ]);
  }

}
