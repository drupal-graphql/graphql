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
      'simple' => '{#graphql a #}',
      'extend' => '{% extends "simple" %}',
      'dynamic_extend' => '{% extends simple %}',
      'override_extend' => '{#graphql b #}{% extends "simple" %}',
      'include' => '{#graphql a #}{% include "sub_fragment" with { foo: "bar" } %}',
      'embed' => '{% embed "embeddable" %}{% block test %} Override {% endblock %}{% endembed %}',
      'embeddable' => '{#graphql a #}{% block test %} Test {% endblock %}',
      'nested_include' => '{#graphql a #}{% include "fragment" with { foo: "bar" } %}',
      'dynamic_include' => '{#graphql a #}{% include sub_fragment with { foo: "bar" } %}',
      'fragment' => '{#graphql b #}{% include "sub_fragment" %}',
      'sub_fragment' => '{#graphql c #}',
    ]));
    $this->twig->addExtension(new GraphQLTwigExtension());
  }

  protected function assertGraphQLQuery($template, $query) {
    $template = $this->twig->loadTemplate($template);
    $this->assertTrue(method_exists($template, 'getGraphQLQuery'));
    $this->assertEquals($query, $template->getGraphQLQuery());
  }

  function testSimple() {
    $this->assertGraphQLQuery('simple', 'a');
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

}