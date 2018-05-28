<?php

namespace Drupal\graphql\Annotation;

/**
 * Annotation for GraphQL subscription plugins.
 *
 * @Annotation
 */
class GraphQLSubscription extends GraphQLAnnotationBase {

  /**
   * {@inheritdoc}
   */
  public $pluginType = GRAPHQL_SUBSCRIPTION_PLUGIN;

  /**
   * The field type.
   *
   * Must be a registered Interface, Type or Scalar.
   *
   * @var string
   */
  public $type = NULL;

  /**
   * The field arguments.
   *
   * Array keyed by argument names with Scalar or Input Type names as values.
   *
   * @var array
   */
  public $arguments = [];

  /**
   * The deprecation reason or FALSE if the field is not deprecated.
   *
   * @var string|bool
   */
  public $deprecated = FALSE;

}
