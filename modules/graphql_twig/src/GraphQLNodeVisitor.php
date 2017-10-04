<?php

namespace Drupal\graphql_twig;

use Twig_Environment;
use Twig_Node;

class GraphQLNodeVisitor extends \Twig_BaseNodeVisitor {


  protected $query = '';
  protected $parent = '';
  protected $includes = [];

  public function getPriority() {
    return 0;
  }

  protected function doEnterNode(Twig_Node $node, Twig_Environment $env) {

    if ($node instanceof \Twig_Node_Module) {

      if ($node->hasNode('parent')) {
        $parent = $node->getNode('parent');
        if ($parent instanceof \Twig_Node_Expression_Constant) {
          $this->parent = $parent->getAttribute('value');
        }
      }

      foreach ($node->getAttribute('embedded_templates') as $embed) {
        $this->doEnterNode($embed, $env);
      }
    }

    if ($node instanceof \Twig_Node_Include && !($node instanceof \Twig_Node_Embed)) {
      $ref = $node->getNode('expr');
      if ($ref instanceof \Twig_Node_Expression_Constant) {
        $this->includes[] = $ref->getAttribute('value');
      }
    }

    if ($node instanceof GraphQLFragmentNode) {
      $this->query .= $node->getFragment();
    }

    return $node;
  }

  protected function doLeaveNode(Twig_Node $node, Twig_Environment $env) {
    if ($node instanceof \Twig_Node_Module) {
      $node->setNode('class_end', new GraphQLNode($this->query, $this->parent, $this->includes));
      $this->query = '';
      $this->parent = '';
      $this->includes = [];
    }
    return $node;
  }

}