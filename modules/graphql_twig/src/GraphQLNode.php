<?php

namespace Drupal\graphql_twig;

use Twig_Compiler;

class GraphQLNode extends \Twig_Node {

  protected $query = "";
  protected $parent = "";
  protected $includes = [];

  /**
   * GraphQLNode constructor.
   *
   * @param string $query
   * @param string $parent
   * @param array $includes
   */
  public function __construct($query, $parent, $includes) {
    $this->query = $query;
    $this->parent = $parent;
    $this->includes = $includes;
    parent::__construct();
  }

  public function compile(Twig_Compiler $compiler) {
    $compiler
      ->write("\nuse \Drupal\graphql_twig\GraphQLTemplateTrait;\n")
      ->write("\nprotected \$graphqlQuery = ")
      ->string($this->query)
      ->write(";\n")
      ->write("\nprotected \$graphqlParent = ")
      ->string($this->parent)
      ->write(";\n")
      ->write("\nprotected \$graphqlIncludes = [");
    foreach ($this->includes as $include) {
      $compiler->string($include)->write(",");
    }
    $compiler->write("];\n");
  }


}