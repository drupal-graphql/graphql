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
use Drupal\graphql\Entity\Server;
use Drupal\Tests\graphql\Traits\QueryResultAssertionTrait;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * @coversDefaultClass \Drupal\graphql\GraphQL\ResolverBuilder
 *
 * @group graphql
 */
class ResolverBuilderTest extends GraphQLTestBase {

  use QueryResultAssertionTrait;

  public function setUp() {
    parent::setUp();

    $gql_schema = <<<GQL
      type Query {
        me: String,
        tree: Tree
      }

      type Tree {
        name: String
        id: Int
      }
GQL;
    $this->mockSchema('graphql_test', $gql_schema);
    $this->mockSchemaPluginManager('graphql_test');

    $this->schemaPluginManager->method('createInstance')
      ->with($this->equalTo('graphql_test'))
      ->will($this->returnValue($this->schema));

    $this->container->set('plugin.manager.graphql.schema', $this->schemaPluginManager);

    Server::create([
      'schema' => 'graphql_test',
      'name' => 'graphql_test',
      'endpoint' => '/graphql_test'
    ])->save();
  }

  /**
   * Return the default schema for this test.
   *
   * @return string
   *   The default schema id.
   */
  protected function getDefaultSchema() {
    return 'graphql_test';
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
      ['entity_load', EntityLoad::class],
      ['entity_id', EntityId::class],
      ['uppercase', Uppercase::class],
    ];
  }

  /**
   * @covers ::fromValue
   */
  public function testFromValue() {
    $this->assertInstanceOf(SdlSchemaPluginBase::class, $this->schema);
    $builder = new ResolverBuilder();

    /*$registry = $this->getMockBuilder(ResolverRegistry::class)
      ->setConstructorArgs([[]])
      ->setMethods([
        'getFieldResolver',
        'getRuntimeFieldResolver',
        'resolveField'
      ])
      ->getMock();

    $registry->expects($this->any())
      ->with($this->anything(), $this->anything())
      ->method('getFieldResolver')
      ->willReturnCallback($builder->fromValue('me'));*/

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

}
