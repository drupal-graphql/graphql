<?php

namespace Drupal\graphql_twig;

class GraphQLTwigExtension extends \Twig_Extension {

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return get_class($this);
  }

  public function getNodeVisitors() {
    return [new GraphQLNodeVisitor()];
  }


}