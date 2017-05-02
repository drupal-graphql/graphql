<?php

namespace Drupal\graphql_core\Annotation;

/**
 * Annotation for GraphQL interface plugins.
 *
 * @Annotation
 */
class GraphQLInterface extends GraphQLAnnotationBase {

  /**
   * {@inheritdoc}
   */
  public $pluginType = GRAPHQL_CORE_INTERFACE_PLUGIN;

  /**
   * The Drupal (TypedData) type of a given interface.
   *
   * @var string
   */
  public $data_type;

  /**
   * The fields attached to this interface.
   *
   * @var array
   */
  public $fields = [];

}
