<?php

namespace Drupal\graphql\Annotation;

/**
 * Annotation for GraphQL field plugins.
 *
 * @Annotation
 */
class GraphQLField extends GraphQLAnnotationBase {

  /**
   * {@inheritdoc}
   */
  public $pluginType = GRAPHQL_FIELD_PLUGIN;

  /**
   * Mark this field as "secure" to be resolved by untrusted consumers.
   *
   * @var bool
   */
  public $secure = FALSE;

  /**
   * The id of the GraphQLType or GraphQLInterface this field is bound to.
   *
   * If omitted, the field is considered a "root" field.
   *
   * @var string[]
   */
  public $parents = [];

  /**
   * The field type.
   *
   * Must be a registered Interface, Type, Scalar or Enum.
   *
   * If an associative array is provided - the Enum type will be created
   * automatically for the given set of values. But $enum_type_name has to be
   * defined in this case.
   *
   * @var string|array
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
   * Contextual arguments.
   *
   * List of argument identifiers that will be merged with the current query
   * context.
   *
   * @var string[]
   */
  public $contextual_arguments = [];

  /**
   * The deprecation reason or FALSE if the field is not deprecated.
   *
   * @var string|bool
   */
  public $deprecated = FALSE;

}
