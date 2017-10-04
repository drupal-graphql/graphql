<?php

namespace Drupal\graphql_twig;

use Twig_Compiler;

/**
 * A Twig node wrapping modules and adding graphql metadata to them.
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
   * @param string $query
   * @param string $parent
   * @param array $includes
   */
  public function __construct($fragment) {
    $this->fragment = $fragment;
    parent::__construct();
  }

  public function getFragment() {
    return $this->fragment;
  }

}