<?php

namespace Drupal\Tests\graphql\Unit;

use Drupal\graphql\Utility\StringHelper;
use Drupal\Tests\UnitTestCase;

/**
 * Tests string helper functions.
 *
 * @group graphql
 */
class StringFormattingTest extends UnitTestCase {

  /**
   * @expectedException \InvalidArgumentException
   * @expectedExceptionMessageRegExp /Failed to create a specification compliant string representation for '.+'\./
   */
  public function testFailureOnInvalidInput() {
    StringHelper::camelCase('123456', '^%!@#&');
  }

  /**
   * @dataProvider providerTestStringFormatting
   */
  public function testCamelCaseFormatting($input, $expected) {
    $this->assertSame($expected, call_user_func_array([StringHelper::class, 'camelCase'], $input));
  }

  /**
   * @dataProvider providerTestStringFormatting
   */
  public function testPropCaseFormatting($input, $expected) {
    $this->assertSame(lcfirst($expected), call_user_func_array([StringHelper::class, 'propCase'], $input));
  }

  public function providerTestStringFormatting() {
    return [
      [['simple-name'], 'SimpleName'],
      [['123-name-with*^&!@some-SPECIAL-chars'], 'NameWithSomeSPECIALChars'],
      [['simple', 'name-of-string', 'components'], 'SimpleNameOfStringComponents'],
      [['123', 'array', '%^!@&#*', 'of', 'STRING', '(*&', 'components', 'with', 'SPEcial', 'chars'], 'ArrayOfSTRINGComponentsWithSPEcialChars']
    ];
  }

}
