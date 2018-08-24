<?php

namespace Drupal\graphql\Annotation;

/**
 * Annotation for GraphQL type plugins.
 *
 * @Annotation
 */
class GraphQLType extends GraphQLAnnotationBase {

  /**
   * {@inheritdoc}
   */
  public $pluginType = GRAPHQL_TYPE_PLUGIN;

  /**
   * The list of interfaces implemented by this type.
   *
   * Fields annotated to interfaces will be inherited.
   *
   * @var array
   */
  public $interfaces = [];

  /**
   * The list of union types containing this type.
   *
   * @var array
   */
  public $unions = [];

  /**
   * The typed data type of this type.
   *
   * @var string|null
   */
  public $type = NULL;

}
