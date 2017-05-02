<?php

namespace Drupal\graphql_core\Annotation;

/**
 * Annotation for GraphQL type plugins.
 *
 * @Annotation
 */
class GraphQLType extends GraphQLAnnotationBase {

  /**
   * {@inheritdoc}
   */
  public $pluginType = GRAPHQL_CORE_TYPE_PLUGIN;

  /**
   * The Drupal (TypedData) type of a given type.
   *
   * @var string
   */
  public $data_type;

  /**
   * The list of interfaces implemented by this type.
   *
   * Fields annotated to interfaces will be inherited.
   *
   * @var array
   */
  public $interfaces = [];

  /**
   * The fields attached to this type.
   *
   * @var array
   */
  public $fields = [];

}
