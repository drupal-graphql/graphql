<?php

namespace Drupal\Tests\graphql_twig\Unit;

use Drupal\graphql_twig\QueryAssembler;
use Drupal\Tests\UnitTestCase;

/**
 * Test the query assembler.
 */
class QueryAssemblerTest extends UnitTestCase {

  /**
   * When there are no fragments, the query is returned untouched.
   */
  public function testNoFragments() {
    $assembler = new QueryAssembler();
    $query = 'query { test }';
    $this->assertEquals($query, $assembler->assemble($query));
  }

  /**
   * Test attachment of the single correct fragment.
   */
  public function testSingleFragment() {
    $assembler = new QueryAssembler();
    $query = 'query { bar { ... a } }';

    $fragment_a = 'fragment a on Foo { bar }';
    $fragment_b = 'fragment b on Foo { bar }';

    $assembler->addFragment('a', $fragment_a);
    $assembler->addFragment('b', $fragment_b);

    $this->assertEquals("$query\n$fragment_a", $assembler->assemble($query));
  }

  /**
   * Test nested fragment resolution.
   */
  public function testNestedFragment() {
    $assembler = new QueryAssembler();
    $query = 'query { foo { ... a } }';

    $fragment_a = 'fragment a on Foo { ... b }';
    $fragment_b = 'fragment b on Foo { bar }';
    $fragment_c = 'fragment c on Foo { bar }';

    $assembler->addFragment('a', $fragment_a);
    $assembler->addFragment('b', $fragment_b);
    $assembler->addFragment('c', $fragment_c);

    $this->assertEquals("$query\n$fragment_a\n$fragment_b", $assembler->assemble($query));
  }

  /**
   * Test if a reference to fragment `a__b` properly falls back to `a`.
   */
  public function testFragmentFallback() {
    $assembler = new QueryAssembler();
    $query = 'query { foo { ... a__b } }';

    $a = 'fragment a on Foo { bar }';

    $assembler->addFragment('a', $a);

    $this->assertEquals(implode("\n", [
      'query { foo { ... a__b } }',
      "fragment a__b on Foo { ... a }",
      "fragment a on Foo {...a__b, bar }",
    ]), $assembler->assemble($query));
  }

  /**
   * Test if fragment `a` automatically includes `a__b`.
   */
  public function testFragmentSuggestion() {
    $assembler = new QueryAssembler();
    $query = 'query { foo { ... a } }';

    $a = 'fragment a on Foo { bar }';
    $a__b = 'fragment a__b on Foo { bar }';

    $assembler->addFragment('a', $a);
    $assembler->addFragment('a__b', $a__b);

    $this->assertEquals(implode("\n", [
      'query { foo { ... a } }',
      "fragment a on Foo {...a__b, bar }",
      "fragment a__b on Foo { bar }",
    ]), $assembler->assemble($query));
  }

}
