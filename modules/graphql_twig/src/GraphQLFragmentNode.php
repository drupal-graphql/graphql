<?php

namespace Drupal\graphql_twig;

/**
 * A Twig node for collecting GraphQL query fragments in twig templates.
 */
class GraphQLFragmentNode extends \Twig_Node {

  /**
   * The fragment string.
   *
   * @var string
   */
  protected $fragment = "";

  /**
   * GraphQLFragmentNode constructor.
   *
   * @param string $fragment
   *   The query fragment.
   */
  public function __construct($fragment) {
    $this->fragment = $fragment;
    parent::__construct();
  }

  /**
   * Retrieve the stored query fragment.
   *
   * @return string
   *   The query fragment.
   */
  public function getFragment() {
    return $this->fragment;
  }

}