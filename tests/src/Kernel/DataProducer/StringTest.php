<?php

namespace Drupal\Tests\graphql\Kernel\DataProducer;

use Drupal\Tests\graphql\Kernel\GraphQLTestBase;

/**
 * Data producers String test class.
 *
 * @group graphql
 */
class StringTest extends GraphQLTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->dataProducerManager = $this->container->get('plugin.manager.graphql.data_producer');
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\String\Uppercase::resolve
   *
   * @dataProvider testUppercaseProvider
   */
  public function testUppercase($input, $expected) {
    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'uppercase',
      'configuration' => []
    ]);
    $this->assertEquals($expected, $plugin->resolve($input));
  }

  public function testUppercaseProvider() {
    return [
      ['test', 'TEST'],
      ['123 ..!!', '123 ..!!'],
      ['test123', 'TEST123']
    ];
  }

}
