<?php

declare(strict_types=1);

namespace Drupal\Tests\graphql\Kernel\DataProducer;

use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use PHPUnit\Framework\Attributes\AllowMockObjectsWithoutExpectations;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Data producers String test class.
 *
 * @group graphql
 */
#[Group('graphql')]
#[RunTestsInSeparateProcesses]
#[AllowMockObjectsWithoutExpectations]
class StringTest extends GraphQLTestBase {

  /**
   * Tests converting a string to uppercase.
   *
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\String\Uppercase::resolve
   *
   * @dataProvider uppercaseProvider
   */
  #[DataProvider('uppercaseProvider')]
  public function testUppercase(string $input, string $expected): void {
    $result = $this->executeDataProducer('uppercase', [
      'string' => $input,
    ]);

    $this->assertEquals($expected, $result);
  }

  /**
   * Tests the upper case data producer.
   */
  public static function uppercaseProvider(): array {
    return [
      ['test', 'TEST'],
      ['123 ..!!', '123 ..!!'],
      ['test123', 'TEST123'],
    ];
  }

}
