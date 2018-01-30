<?php

namespace Drupal\graphql\Annotation;

/**
 * Annotation for GraphQL scalar plugins.
 *
 * @Annotation
 */
class GraphQLScalar extends GraphQLAnnotationBase {

  /**
   * {@inheritdoc}
   */
  public $pluginType = GRAPHQL_SCALAR_PLUGIN;

  /**
   * The typed data type of this type.
   *
   * @var string|null
   */
  public $type = NULL;

}
