<?php

namespace Drupal\Tests\graphql_twig;

use Drupal\graphql_twig\GraphQLTwigExtension;
use Drupal\Tests\UnitTestCase;

class GraphQLTwigExtensionTest extends UnitTestCase {

  /**
   * The twig environment.
   *
   * @var \Twig_Environment
   */
  protected $twig;

  function setUp() {
    $this->twig = new \Twig_Environment(new\Twig_Loader_Array([
      'query' => '{% graphql %}query ($arg: String!) { foo(id: [1, 2, 3], search: "test") { bar } }{% endgraphql %}',
      'simple' => '{% graphql %}a{% endgraphql %}',
      'extend' => '{% extends "simple" %}',
      'dynamic_extend' => '{% extends simple %}',
      'override_extend' => '{% graphql %}b{% endgraphql %}{% extends "simple" %}',
      'include' => '{% graphql %}a{% endgraphql %}{% include "sub_fragment" with { foo: "bar" } %}',
      'embed' => '{% embed "embeddable" %}{% block test %} Override {% endblock %}{% endembed %}',
      'embeddable' => '{% graphql %}a{% endgraphql %}{% block test %} Test {% endblock %}',
      'nested_include' => '{% graphql %}a{% endgraphql %}{% include "fragment" with { foo: "bar" } %}',
      'dynamic_include' => '{% graphql %}a{% endgraphql %}{% include sub_fragment with { foo: "bar" } %}',
      'fragment' => '{% graphql %}b{% endgraphql %}{% include "sub_fragment" %}',
      'sub_fragment' => '{% graphql %}c{% endgraphql %}',
      'extend_include' => '{% graphql %}a{% endgraphql %}{% extends "fragment" %}'
    ]));
    $this->twig->addExtension(new GraphQLTwigExtension());
  }

  protected function assertGraphQLQuery($template, $query) {
    $template = $this->twig->loadTemplate($template);
    $this->assertTrue(method_exists($template, 'getGraphQLQuery'));
    $this->assertEquals($query, $template->getGraphQLQuery());
  }

  function testQuery() {
    $this->assertGraphQLQuery('query', 'query ($arg: String!) { foo(id: [1, 2, 3], search: "test") { bar } }');
  }

  function testExtend() {
    $this->assertGraphQLQuery('extend', 'a');
  }

  function testDynamicExtend() {
    $this->assertGraphQLQuery('dynamic_extend', '');
  }

  function testInclude() {
    $this->assertGraphQLQuery('include', "a\nc");
  }

  function testEmbed() {
    $this->assertGraphQLQuery('embed', "a");
  }

  function testNestedInclude() {
    $this->assertGraphQLQuery('nested_include', "a\nb\nc");
  }

  function testDynamicInclude() {
    $this->assertGraphQLQuery('dynamic_include', "a");
  }

  function testExtendInclude() {
    $this->assertGraphQLQuery('extend_include', "a\nc");
  }

}