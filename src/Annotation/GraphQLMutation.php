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
   * Cardinality.
   *
   * Defines if the field is a multi or single value field.
   *
   * @var bool
   */
  public $multi = FALSE;

  /**
   * Nullable state.
   *
   * Define if the field is nullable.
   *
   * @var bool
   */
  public $nullable = FALSE;

  /**
   * The field arguments.
   *
   * Array keyed by argument names with Scalar or Input Type names as values.
   *
   * @var array
   */
  public $arguments = [];

}
