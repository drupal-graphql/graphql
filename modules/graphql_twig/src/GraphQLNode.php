<?php

namespace Drupal\graphql_twig;

use Twig_Compiler;

/**
 * GraphQL meta information Twig node.
 *
 * A Twig node that will be attached to templates `class_end` to output the
 * collected graphql query and inheritance metadata. Not parsed directly but
 * injected by the `GraphQLNodeVisitor`.
 */
class GraphQLNode extends \Twig_Node {

  /**
   * The modules query string.
   *
   * @var string
   */
  protected $query = "";

  /**
   * The modules parent class.
   *
   * @var string
   */
  protected $parent = "";

  /**
   * The modules includes.
   * @var array
   */
  protected $includes = [];

  /**
   * GraphQLNode constructor.
   *
   * @param string $query
   *   The query string.
   * @param string $parent
   *   The parent template identifier.
   * @param array $includes
   *   Identifiers for any included/referenced templates.
   */
  public function __construct($query, $parent, $includes) {
    $this->query = $query;
    $this->parent = $parent;
    $this->includes = $includes;
    parent::__construct();
  }

  /**
   * {@inheritdoc}
   */
  public function compile(Twig_Compiler $compiler) {
    $compiler
      // Make the template implement the GraphQLTemplateTrait.
      ->write("\nuse \Drupal\graphql_twig\GraphQLTemplateTrait;\n")
      // Write metadata properties.
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