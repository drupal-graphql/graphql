<?php

declare(strict_types=1);

namespace Drupal\Tests\graphql\Kernel;

use Drupal\graphql\GraphQL\Resolver\ResolverInterface;
use GraphQL\Deferred;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests that the resolver builder behaves correctly.
 *
 * @coversDefaultClass \Drupal\graphql\GraphQL\ResolverBuilder
 *
 * @group graphql
 */
#[Group('graphql')]
#[RunTestsInSeparateProcesses]
#[AllowMockObjectsWithoutExpectations]
class ResolverBuilderTest extends ResolverBuilderTestBase {

  /**
   * Tests producing plugins from builder input.
   *
   * @covers ::produce
   *
   * @dataProvider builderProducingProvider
   */
  #[DataProvider('builderProducingProvider')]
  public function testBuilderProducing(string $input, string $expected): void {
    $plugin = $this->builder->produce($input, []);
    $this->assertInstanceOf($expected, $plugin);
  }

  /**
   * Data provider for testBuilderProducing().
   */
  public static function builderProducingProvider(): array {
    return [
      ['entity_load', ResolverInterface::class],
      ['entity_id', ResolverInterface::class],
      ['uppercase', ResolverInterface::class],
    ];
  }

  /**
   * @covers ::produce
   */
  public function testBuilderProducingDeferredArgument(): void {
    $this->mockResolver('Query', 'tree', $this->builder->fromValue([]));

    $this->mockResolver('Tree', 'name', $this->builder->produce('uppercase')
      ->map('string', $this->builder->fromValue(
          new Deferred(static function () {
            return 'Some tree name';
          })
        )
      ));

    $query = <<<GQL
      query {
        tree {
          name
        }
      }
GQL;

    $this->assertResults($query, [], ['tree' => ['name' => 'SOME TREE NAME']]);
  }

  /**
   * @covers ::fromValue
   */
  public function testFromValue(): void {
    $this->mockResolver('Query', 'me', $this->builder->fromValue('some me'));

    $query = <<<GQL
      query {
        me
      }
GQL;

    $this->assertResults($query, [], ['me' => 'some me']);
  }

  /**
   * @covers ::fromParent
   */
  public function testFromParent(): void {
    $this->mockResolver('Query', 'tree', $this->builder->fromValue('Some string value'));
    $this->mockResolver('Tree', 'name', $this->builder->fromParent());

    $query = <<<GQL
      query {
        tree {
          name
        }
      }
GQL;

    $this->assertResults($query, [], ['tree' => ['name' => 'Some string value']]);
  }

  /**
   * @covers ::fromArgument
   */
  public function testFromArgument(): void {
    $this->mockResolver('Query', 'tree', $this->builder->fromValue([
      'name' => 'some tree',
      'id' => 5,
    ]));
    $this->mockResolver('Tree', 'id', $this->builder->fromArgument('someArg'));

    $query = <<<GQL
      query {
        tree(id: 5) {
          id(someArg: 234)
        }
      }
GQL;

    $this->assertResults($query, [], ['tree' => ['id' => 234]]);
  }

  /**
   * @covers ::fromPath
   */
  public function testFromPath(): void {
    $manager = $this->container->get('typed_data_manager');
    $tree_definition = $manager->createDataDefinition('tree');
    /** @var \Drupal\graphql_resolver_builder_test\Plugin\DataType\Tree $right */
    $right = $manager->create($tree_definition);
    $right->set('value', 'Front page');
    /** @var \Drupal\graphql_resolver_builder_test\Plugin\DataType\Tree $tree */
    $tree = $manager->create($tree_definition);
    $tree->set('left', [
      'value' => '<front>',
      'right' => $right,
    ]);

    $this->mockResolver('Query', 'tree', $this->builder->fromValue($tree));
    $this->mockResolver('Tree', 'uri', $this->builder->fromPath('tree', 'left.value'));

    $query = <<<GQL
      query {
        tree {
          uri
        }
      }
GQL;

    $this->assertResults($query, [], ['tree' => ['uri' => '<front>']]);
  }

  /**
   * @covers ::compose
   */
  public function testCompose(): void {
    $this->mockResolver('Query', 'tree', $this->builder->fromValue([
      'name' => 'some tree',
      'id' => 5,
    ]));
    $this->mockResolver('Tree', 'name', $this->builder->compose(
      $this->builder->fromValue('Some tree name'),
      $this->builder->produce('uppercase')
        ->map('string', $this->builder->fromParent())
    ));

    $query = <<<GQL
      query {
        tree {
          name
        }
      }
GQL;

    $this->assertResults($query, [], ['tree' => ['name' => 'SOME TREE NAME']]);
  }

  /**
   * @covers ::compose
   */
  public function testComposeNullValue(): void {
    $this->mockResolver('Query', 'tree', $this->builder->fromValue([
      'name' => 'some tree',
      'id' => 5,
    ]));
    $this->mockResolver('Tree', 'name', $this->builder->compose(
      $this->builder->fromValue(NULL),
      $this->builder->produce('uppercase')
        ->map('string', $this->builder->fromParent())
    ));

    $query = <<<GQL
      query {
        tree {
          name
        }
      }
GQL;

    $this->assertResults($query, [], ['tree' => ['name' => NULL]]);
  }

  /**
   * @covers ::context
   * @covers ::fromContext
   */
  public function testFromContext(): void {
    $this->mockResolver('Query', 'tree', $this->builder->fromValue('some value'));

    $this->mockResolver('Tree', 'context', $this->builder->compose(
      $this->builder->context('my context', $this->builder->fromValue('my context value')),
      $this->builder->fromValue('some language value')
    ));

    $this->mockResolver('Context', 'myContext', $this->builder->fromContext('my context'));

    $query = <<<GQL
      query {
        tree {
          context {
            myContext
          }
        }
      }
GQL;

    $this->assertResults($query, [], ['tree' => ['context' => ['myContext' => 'my context value']]]);
  }

}
