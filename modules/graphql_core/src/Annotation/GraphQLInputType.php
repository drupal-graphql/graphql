<?php

namespace Drupal\graphql_core\Annotation;

/**
 * Annotation for GraphQL input type plugins.
 *
 * @Annotation
 */
class GraphQLInputType extends GraphQLAnnotationBase {

  /**
   * {@inheritdoc}
   */
  public $pluginType = GRAPHQL_CORE_INPUT_TYPE_PLUGIN;

  /**
   * The fields attached to this type.
   *
   * @var array
   */
  public $fields = [];

}
