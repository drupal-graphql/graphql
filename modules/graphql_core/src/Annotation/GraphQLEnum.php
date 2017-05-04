<?php

namespace Drupal\graphql_core\Annotation;

/**
 * Annotation for GraphQL scalar plugins.
 *
 * @Annotation
 */
class GraphQLEnum extends GraphQLAnnotationBase {

  /**
   * {@inheritdoc}
   */
  public $pluginType = GRAPHQL_CORE_ENUM_PLUGIN;

}
