<?php

namespace Drupal\graphql_twig;

use Twig_Environment;
use Twig_Node;

class GraphQLNodeVisitor extends \Twig_BaseNodeVisitor {

  public static $GRAPHQL_TWIG_REGEX = '/.*\{#graphql\s+(?<query>.*)\s+#\}.*/s';

  protected $query = '';
  protected $parent = '';
  protected $includes = [];

  public function getPriority() {
    return 0;
  }

  protected function doEnterNode(Twig_Node $node, Twig_Environment $env) {

    if ($node instanceof \Twig_Node_Module) {

      $this->query = '';
      $this->parent = '';
      $this->includes = [];

      $source = $node->getAttribute('source');
      preg_match(static::$GRAPHQL_TWIG_REGEX, $source, $matches);

      if (array_key_exists('query', $matches)) {
        $this->query = $matches['query'];
      }

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

    return $node;
  }

  protected function doLeaveNode(Twig_Node $node, Twig_Environment $env) {
    if ($node instanceof \Twig_Node_Module) {
      $node->setNode('class_end', new GraphQLNode($this->query, $this->parent, $this->includes));
    }
    return $node;
  }

}