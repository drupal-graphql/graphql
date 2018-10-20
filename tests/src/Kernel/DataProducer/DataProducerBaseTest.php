<?php

namespace Drupal\Tests\graphql\Kernel\DataProducer;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Drupal\typed_data\Context\ContextDefinition;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Test the data producer base class.
 *
 * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase
 * @group graphql
 */
class DataProducerBaseTest extends GraphQLTestBase {

  /**
   * Test a simple data producer without arguments.
   */
  public function testSimpleDataProducer() {
    $mock = $this->getMockBuilder(DataProducerPluginBase::class)
      ->setConstructorArgs([
        [],
        'test',
        []
      ])
      ->setMethods(['resolve'])
      ->getMockForAbstractClass();

    $mock
      ->expects(static::once())
      ->method('resolve')
      ->willReturn('foo');

    $this->assertEquals('foo', call_user_func($mock, NULL, [], new ResolveContext(), new ResolveInfo([])));
  }

  /**
   * Test a simple data producer with arguments.
   *
   * @throws \Exception
   */
  public function testDataProducerArguments() {
    $builder = new ResolverBuilder();
    $mock = $this->getMockBuilder(DataProducerPluginBase::class)
      ->setConstructorArgs([
        ['mapping' => [
          'a' => $builder->fromValue('A'),
          'b' => $builder->fromValue('B'),
        ]],
        'test',
        ['consumes' => [
          'a' => new ContextDefinition(),
          'b' => new ContextDefinition(),
        ]]
      ])
      ->setMethods(['resolve'])
      ->getMockForAbstractClass();

    $mock
      ->expects(static::once())
      ->method('resolve')
      ->willReturnCallback(function ($a, $b) {
        return "$a and $b";
      });

    $this->assertEquals('A and B', call_user_func($mock, NULL, [], new ResolveContext(), new ResolveInfo([])));
  }


  /**
   * Test if an uncached data producer is invoked twice.
   */
  public function testDataProducerWithoutCaching() {
    $mock = $this->getMockBuilder(DataProducerPluginBase::class)
      ->setConstructorArgs([
        [],
        'test',
        []
      ])
      ->setMethods(['resolve'])
      ->getMockForAbstractClass();

    $mock
      ->expects(static::exactly(2))
      ->method('resolve')
      ->willReturn('foo');

    for ($i = 0; $i < 2; $i++) {
      call_user_func($mock, NULL, [], new ResolveContext(), new ResolveInfo([]));
    }
  }

  /**
   * Test if an cached data producer is invoked only once.
   */
  public function testDataProducerWithCaching() {
    $mock = $this->getMockBuilder(DataProducerPluginBase::class)
      ->setConstructorArgs([
        ['cache' => TRUE],
        'test',
        []
      ])
      ->setMethods(['resolve'])
      ->getMockForAbstractClass();

    $mock
      ->expects(static::exactly(1))
      ->method('resolve')
      ->willReturn('foo');

    for ($i = 0; $i < 2; $i++) {
      call_user_func($mock, NULL, [], new ResolveContext(), new ResolveInfo([]));
    }
  }


}
