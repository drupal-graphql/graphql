<?php

namespace Drupal\graphql_core\Annotation;

/**
 * Annotation for GraphQL field plugins.
 *
 * @Annotation
 */
class GraphQLField extends GraphQLAnnotationBase  {

  /**
   * {@inheritdoc}
   */
  public $pluginType = GRAPHQL_CORE_FIELD_PLUGIN;

  /**
   * The id of the GraphQLType or GraphQLInterface this field is bound to.
   *
   * If omitted, the field is considered a "root" field.
   *
   * @var string[]
   */
  public $types = [];

  /**
   * The field type.
   *
   * Must be a registered Interface, Type, Scalar or Enum.
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
  public $nullable = TRUE;

  /**
   * The field arguments.
   *
   * Array keyed by argument names with Scalar or Input Type names as values.
   *
   * @var array
   */
  public $arguments = [];

}
