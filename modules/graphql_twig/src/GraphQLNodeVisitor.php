<?php

namespace Drupal\graphql_twig;

use Twig_Environment;
use Twig_Node;

/**
 * Scans a Twig template for query fragments and references to other templates.
 */
class GraphQLNodeVisitor extends \Twig_BaseNodeVisitor {

  /**
   * The query string.
   *
   * @var string
   */
  protected $query = '';

  /**
   * The parent template identifier.
   *
   * @var string
   */
  protected $parent = '';

  /**
   * A list of referenced templates (include, embed).
   *
   * @var string[]
   */
  protected $includes = [];

  /**
   * {@inheritdoc}
   */
  public function getPriority() {
    return 0;
  }

  /**
   * {@inheritdoc}
   */
  protected function doEnterNode(Twig_Node $node, Twig_Environment $env) {

    if ($node instanceof \Twig_Node_Module) {

      // If there is a parent node (created by `extends` or `embed`),
      // store it's identifier.
      if ($node->hasNode('parent')) {
        $parent = $node->getNode('parent');
        if ($parent instanceof \Twig_Node_Expression_Constant) {
          $this->parent = $parent->getAttribute('value');
        }
      }

      // Recurse into embedded templates.
      foreach ($node->getAttribute('embedded_templates') as $embed) {
        $this->doEnterNode($embed, $env);
      }
    }

    // Store identifiers of any static includes.
    // There is no way to make this work for dynamic includes.
    if ($node instanceof \Twig_Node_Include && !($node instanceof \Twig_Node_Embed)) {
      $ref = $node->getNode('expr');
      if ($ref instanceof \Twig_Node_Expression_Constant) {
        $this->includes[] = $ref->getAttribute('value');
      }
    }

    // When encountering a GraphQL fragment, add it to the current query.
    if ($node instanceof GraphQLFragmentNode) {
      $this->query .= $node->getFragment();
    }

    return $node;
  }

  /**
   * {@inheritdoc}
   */
  protected function doLeaveNode(Twig_Node $node, Twig_Environment $env) {
    if ($node instanceof \Twig_Node_Module) {
      // Store current query information to be compiled into the templates
      // `class_end`.
      $node->setNode('class_end', new GraphQLNode($this->query, $this->parent, $this->includes));

      // Reset query information for the next module.
      $this->query = '';
      $this->parent = '';
      $this->includes = [];
    }
    return $node;
  }

}