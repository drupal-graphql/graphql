<?php

namespace Drupal\graphql_core\Annotation;

/**
 * Annotation for GraphQL scalar plugins.
 *
 * @Annotation
 */
class GraphQLScalar extends GraphQLAnnotationBase {

  /**
   * {@inheritdoc}
   */
  public $pluginType = GRAPHQL_CORE_SCALAR_PLUGIN;

  /**
   * The Drupal (TypedData) type of a given scalar.
   *
   * @var string
   */
  public $data_type;

}
