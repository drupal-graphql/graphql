<?php

namespace Drupal\graphql\Annotation;

/**
 * Annotation for GraphQL mutation plugins.
 *
 * @Annotation
 */
class GraphQLMutation extends GraphQLAnnotationBase {

  /**
   * {@inheritdoc}
   */
  public $pluginType = GRAPHQL_MUTATION_PLUGIN;

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
