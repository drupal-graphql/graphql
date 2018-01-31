<?php

namespace Drupal\graphql\Annotation;

/**
 * Annotation for GraphQL union type plugins.
 *
 * @Annotation
 */
class GraphQLUnionType extends GraphQLAnnotationBase {

  /**
   * {@inheritdoc}
   */
  public $pluginType = GRAPHQL_UNION_TYPE_PLUGIN;

  /**
   * The list of types this union type contains.
   *
   * @var string[]
   */
  public $types = [];

  /**
   * The typed data type of this type.
   *
   * @var string|null
   */
  public $type = NULL;

}
