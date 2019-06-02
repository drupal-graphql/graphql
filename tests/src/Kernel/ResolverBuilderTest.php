<?php

namespace Drupal\Tests\graphql\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistry;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\graphql\Plugin\GraphQL\Schema\SdlSchemaPluginBase;
use Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityLoad;
use Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\EntityId;
use Drupal\graphql\Plugin\GraphQL\DataProducer\String\Uppercase;
use Drupal\Tests\graphql\Traits\QueryResultAssertionTrait;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Deferred;
use GraphQL\Type\Definition\ResolveInfo;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\graphql\GraphQL\Resolver\ResolverInterface;

/**
 * @coversDefaultClass \Drupal\graphql\GraphQL\ResolverBuilder
 *
 * @requires module typed_data
 *
 * @group graphql
 */
class ResolverBuilderTest extends GraphQLTestBase {

  use QueryResultAssertionTrait;

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();

    $gql_schema = <<<GQL
      type Query {
        me: String
        tree(id: Int): Tree
      }

      type Tree {
        id(someArg: Int): Int
        name: String
        uri: String
        language: Language
      }

      type Language {
        languageContext: String
      }
GQL;

    $this->setUpSchema($gql_schema, $this->getDefaultSchema());
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
    $builder = new ResolverBuilder();
    $plugin = $builder->produce($input, []);
    $this->assertInstanceOf($expected, $plugin);
  }

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
    $builder = new ResolverBuilder();

    $registry = new ResolverRegistry([]);
    $registry->addFieldResolver('Query', 'me', $builder->fromValue('some me'));
    $this->schema->expects($this->any())
      ->method('getResolverRegistry')
      ->willReturn($registry);

    $query = <<<GQL
      query {
        me
      }
GQL;

    $this->assertResults($query, [], ['me' => 'some me'], $this->defaultCacheMetaData());
  }

  /**
   * @covers ::fromParent
   */
  public function testFromParent() {
    $builder = new ResolverBuilder();

    $registry = new ResolverRegistry([]);

    $registry->addFieldResolver('Query', 'tree', $builder->fromValue('Some string value'));

    $registry->addFieldResolver('Tree', 'name', $builder->fromParent());

    $this->schema->expects($this->any())
      ->method('getResolverRegistry')
      ->willReturn($registry);

    $query = <<<GQL
      query {
        tree {
          name
        }
      }
GQL;

    $this->assertResults($query, [], ['tree' => ['name' => 'Some string value']], $this->defaultCacheMetaData());
  }

  /**
   * @covers ::fromArgument
   */
  public function testFromArgument() {
    $builder = new ResolverBuilder();

    $registry = new ResolverRegistry([]);

    $registry->addFieldResolver('Query', 'tree', $builder->fromValue(['name' => 'some tree', 'id' => 5]));

    $registry->addFieldResolver('Tree', 'id', $builder->fromArgument('someArg'));

    $this->schema->expects($this->any())
      ->method('getResolverRegistry')
      ->willReturn($registry);

    $query = <<<GQL
      query {
        tree(id: 5) {
          id(someArg: 234)
        }
      }
GQL;

    $this->assertResults($query, [], ['tree' => ['id' => 234]], $this->defaultCacheMetaData());
  }

  /**
   * @covers ::fromPath
   */
  public function testFromPath() {
    $builder = new ResolverBuilder();
    $registry = new ResolverRegistry([]);

    $typed_data_manager = $this->getMock(TypedDataManagerInterface::class);

    $typed_data_manager->expects($this->any())
      ->method('getDefinition')
      ->will($this->returnValueMap([
        'tree' => ['class' => '\Drupal\Core\TypedData\ComplexDataInterface'],
      ]));

    $uri = $this->prophesize(TypedDataInterface::class);
    $uri->getValue()
      ->willReturn('<front>');

    $path = $this->prophesize(ComplexDataInterface::class);
    $path->get('uri')
      ->willReturn($uri);
    $path->getValue()
      ->willReturn([]);

    $tree_type = $this->prophesize(ComplexDataInterface::class);
    $tree_type->get('path')
      ->willReturn($path);
    $tree_type->getValue()
      ->willReturn([]);

    $typed_data_manager->expects($this->any())
      ->method('create')
      ->willReturn($tree_type->reveal());

    $this->container->set('typed_data_manager', $typed_data_manager);

    $registry->addFieldResolver('Query', 'tree', $builder->fromValue([
      'path' => [
        'uri' => '<front>',
        'path_name' => 'Front page',
      ]
    ]));

    $registry->addFieldResolver('Tree', 'uri', $builder->fromPath('tree', 'path.uri'));

    $this->schema->expects($this->any())
      ->method('getResolverRegistry')
      ->willReturn($registry);

    $query = <<<GQL
      query {
        tree {
          uri
        }
      }
GQL;

    $this->assertResults($query, [], ['tree' => ['uri' => '<front>']], $this->defaultCacheMetaData());
  }

  /**
   * @covers ::compose
   */
  public function testCompose() {
    $builder = new ResolverBuilder();
    $registry = new ResolverRegistry([]);

    $registry->addFieldResolver('Query', 'tree', $builder->fromValue(['name' => 'some tree', 'id' => 5]));

    $registry->addFieldResolver('Tree', 'name', $builder->compose(
      $builder->fromValue('Some tree name'),
      $builder->produce('uppercase', ['mapping' => [
        'string' => $builder->fromParent(),
      ]])
    ));

    $this->schema->expects($this->any())
      ->method('getResolverRegistry')
      ->willReturn($registry);

    $query = <<<GQL
      query {
        tree {
          name
        }
      }
GQL;

    $this->assertResults($query, [], ['tree' => ['name' => 'SOME TREE NAME']], $this->defaultCacheMetaData());
  }

  /**
   * @covers ::context
   * @covers ::fromContext
   */
  public function testFromContext() {
    $builder = new ResolverBuilder();
    $registry = new ResolverRegistry([]);

    $registry->addFieldResolver('Query', 'tree', $builder->fromValue('some value'));

    $registry->addFieldResolver('Tree', 'language', $builder->compose(
      $builder->context('language_context', $builder->fromValue('language context value')),
      $builder->fromValue('some language value')
    ));

    $registry->addFieldResolver('Language', 'languageContext', $builder->fromContext('language_context'));

    $this->schema->expects($this->any())
      ->method('getResolverRegistry')
      ->willReturn($registry);

    $query = <<<GQL
      query {
        tree {
          language {
            languageContext
          }
        }
      }
GQL;

    $this->assertResults($query, [], ['tree' => ['language' => ['languageContext' => 'language context value']]], $this->defaultCacheMetaData());
  }

  /**
   * @covers ::cond
   */
  public function testSimpleCond() {
    $builder = new ResolverBuilder();
    $registry = new ResolverRegistry([]);

    $registry->addFieldResolver('Query', 'tree', $builder->fromValue(['name' => 'some tree', 'id' => 5]));

    $registry->addFieldResolver('Tree', 'name', $builder->cond([
      [$builder->fromValue(FALSE), $builder->fromValue('This should not be in the result.')],
      [$builder->fromValue(TRUE), $builder->fromValue('But this should.')],
      [$builder->fromValue(TRUE), $builder->fromValue('And this not, event though its true.')],
    ]));

    $this->schema->expects($this->any())
      ->method('getResolverRegistry')
      ->willReturn($registry);

    $query = <<<GQL
      query {
        tree {
          name
        }
      }
GQL;

    $this->assertResults($query, [], ['tree' => ['name' => 'But this should.']], $this->defaultCacheMetaData());
  }

  /**
   * @covers ::cond
   */
  public function testDeferredCond() {
    $builder = new ResolverBuilder();
    $registry = new ResolverRegistry([]);

    $registry->addFieldResolver('Query', 'tree', $builder->fromValue(['name' => 'some tree', 'id' => 5]));

    $registry->addFieldResolver('Tree', 'name', $builder->cond([
      [$builder->fromValue(FALSE), $builder->fromValue('This should not be in the result.')],
      [function () { return new Deferred(function () { return TRUE; }); }, $builder->fromValue('But this should.')],
      [$builder->fromValue(TRUE), $builder->fromValue('And this not, event though its true.')],
    ]));

    $this->schema->expects($this->any())
      ->method('getResolverRegistry')
      ->willReturn($registry);

    $query = <<<GQL
      query {
        tree {
          name
        }
      }
GQL;

    $this->assertResults($query, [], ['tree' => ['name' => 'But this should.']], $this->defaultCacheMetaData());
  }

  /**
   * @covers ::cond
   */
  public function testParentCond() {
    $builder = new ResolverBuilder();
    $registry = new ResolverRegistry([]);

    $registry->addFieldResolver('Query', 'tree', $builder->fromValue(['name' => 'some tree', 'id' => 5]));

    $registry->addFieldResolver('Tree', 'name', $builder->cond([
      [$builder->fromValue(FALSE), $builder->fromValue('This should not be in the result.')],
      [$builder->fromParent(), $builder->fromValue('But this should.')],
      [$builder->fromValue(TRUE), $builder->fromValue('And this not, event though its true.')],
    ]));

    $this->schema->expects($this->any())
      ->method('getResolverRegistry')
      ->willReturn($registry);

    $query = <<<GQL
      query {
        tree {
          name
        }
      }
GQL;

    $this->assertResults($query, [], ['tree' => ['name' => 'But this should.']], $this->defaultCacheMetaData());
  }
}

