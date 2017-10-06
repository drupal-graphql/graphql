<?php

namespace Drupal\graphql_twig;

/**
 * Simple Twig extension to integrate GraphQL.
 */
class GraphQLTwigExtension extends \Twig_Extension {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return get_class($this);
  }

  /**
   * {@inheritdoc}
   */
  public function getTokenParsers() {
    return [new GraphQLTokenParser()];
  }

  /**
   * {@inheritdoc}
   */
  public function getNodeVisitors() {
    return [new GraphQLNodeVisitor()];
  }

}