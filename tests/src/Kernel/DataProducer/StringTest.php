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
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\String\Uppercase::resolve
   *
   * @dataProvider testUppercaseProvider
   */
  public function testUppercase($input, $expected) {
    $result = $this->executeDataProducer('uppercase', [
      'string' => $input,
    ]);

    $this->assertEquals($expected, $result);
  }

  public function testUppercaseProvider() {
    return [
      ['test', 'TEST'],
      ['123 ..!!', '123 ..!!'],
      ['test123', 'TEST123']
    ];
  }

}
