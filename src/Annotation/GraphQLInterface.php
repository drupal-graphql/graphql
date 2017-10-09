<?php

namespace Drupal\graphql\Annotation;

/**
 * Annotation for GraphQL interface plugins.
 *
 * @Annotation
 */
class GraphQLInterface extends GraphQLAnnotationBase {

  /**
   * {@inheritdoc}
   */
  public $pluginType = GRAPHQL_INTERFACE_PLUGIN;

  /**
   * The Drupal (TypedData) type of a given interface.
   *
   * @var string
   */
  public $data_type;

  /**
   * The list of parent interfaces this interface extends.
   *
   * Fields attached to interfaces will be inherited.
   *
   * @var string[]
   */
  public $interfaces = [];

}
