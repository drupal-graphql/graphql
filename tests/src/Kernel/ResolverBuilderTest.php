<?php

namespace Drupal\Tests\graphql\Kernel;

use GraphQL\Deferred;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\graphql\GraphQL\Resolver\ResolverInterface;

/**
 * @coversDefaultClass \Drupal\graphql\GraphQL\ResolverBuilder
 *
 * @group graphql
 */
class ResolverBuilderTest extends GraphQLTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'graphql',
    'typed_data',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $schema = <<<GQL
      type Query {
        me: String
        tree(id: Int): Tree
      }

      type Tree {
        id(someArg: Int): Int
        name: String
        uri: String
        context: Context
      }

      type Context {
        myContext: String
      }
GQL;

    $this->setUpSchema($schema);
  }

  /**
   * @covers ::produce
   *
   * @dataProvider testBuilderProducingProvider
   *
   * @param $input
   * @param $expected
   */
  public function testBuilderProducing($input, $expected) {
    $plugin = $this->builder->produce($input, []);
    $this->assertInstanceOf($expected, $plugin);
  }

  /**
   * @return array
   */
  public function testBuilderProducingProvider() {
    return [
      ['entity_load', ResolverInterface::class],
      ['entity_id', ResolverInterface::class],
      ['uppercase', ResolverInterface::class],
    ];
  }

  /**
   * @covers ::fromValue
   */
  public function testFromValue() {
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
  public function testFromParent() {
    $this->mockResolver('Query', 'tree',$this->builder->fromValue('Some string value'));
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
  public function testFromArgument() {
    $this->mockResolver('Query', 'tree', $this->builder->fromValue(['name' => 'some tree', 'id' => 5]));
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
  public function testFromPath() {
    $manager = $this->createMock(TypedDataManagerInterface::class);
    $manager->expects($this->any())
      ->method('getDefinition')
      ->will($this->returnValueMap([
        'tree_mock' => ['class' => '\Drupal\Core\TypedData\ComplexDataInterface'],
      ]));

    $this->container->set('typed_data_manager', $manager);

    $uri = $this->prophesize(TypedDataInterface::class);
    $uri->getValue()->willReturn('<front>');

    $path = $this->prophesize(ComplexDataInterface::class);
    $path->get('uri')->willReturn($uri);
    $path->getValue()->willReturn([]);

    $tree = $this->prophesize(ComplexDataInterface::class);
    $tree->get('path')->willReturn($path);
    $tree->getValue()->willReturn([]);

    $manager->expects($this->any())
      ->method('create')
      ->willReturn($tree->reveal());

    $this->mockResolver('Query', 'tree', $this->builder->fromValue([
      'path' => [
        'uri' => '<front>',
        'path_name' => 'Front page',
      ],
    ]));

    $this->mockResolver('Tree', 'uri', $this->builder->fromPath('tree', 'path.uri'));

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
  public function testCompose() {
    $this->mockResolver('Query', 'tree', $this->builder->fromValue(['name' => 'some tree', 'id' => 5]));
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
   * @covers ::context
   * @covers ::fromContext
   */
  public function testFromContext() {
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

  /**
   * @covers ::cond
   */
  public function testSimpleCond() {
    $this->mockResolver('Query', 'tree', $this->builder->fromValue(['name' => 'some tree', 'id' => 5]));
    $this->mockResolver('Tree', 'name', $this->builder->cond([
      [$this->builder->fromValue(FALSE), $this->builder->fromValue('This should not be in the result.')],
      [$this->builder->fromValue(TRUE), $this->builder->fromValue('But this should.')],
      [$this->builder->fromValue(TRUE), $this->builder->fromValue('And this not, event though its true.')],
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
  public function testDeferredCond() {
    $this->mockResolver('Query', 'tree', $this->builder->fromValue(['name' => 'some tree', 'id' => 5]));
    $this->mockResolver('Tree', 'name', $this->builder->cond([
      [$this->builder->fromValue(FALSE), $this->builder->fromValue('This should not be in the result.')],
      [function () { return new Deferred(function () { return TRUE; }); }, $this->builder->fromValue('But this should.')],
      [$this->builder->fromValue(TRUE), $this->builder->fromValue('And this not, event though its true.')],
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
  public function testParentCond() {
    $this->mockResolver('Query', 'tree', ['name' => 'some tree', 'id' => 5]);
    $this->mockResolver('Tree', 'name', $this->builder->cond([
      [$this->builder->fromValue(FALSE), $this->builder->fromValue('This should not be in the result.')],
      [$this->builder->fromParent(), $this->builder->fromValue('But this should.')],
      [$this->builder->fromValue(TRUE), $this->builder->fromValue('And this not, event though its true.')],
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
}

