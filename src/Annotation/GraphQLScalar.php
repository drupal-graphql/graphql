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
   * The Drupal (TypedData) type of a given scalar.
   *
   * @var string
   */
  public $data_type;

}
