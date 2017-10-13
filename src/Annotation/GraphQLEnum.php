<?php

namespace Drupal\graphql\Annotation;

/**
 * Annotation for GraphQL scalar plugins.
 *
 * @Annotation
 */
class GraphQLEnum extends GraphQLAnnotationBase {

  /**
   * {@inheritdoc}
   */
  public $pluginType = GRAPHQL_ENUM_PLUGIN;

}
