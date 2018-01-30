<?php

namespace Drupal\graphql\Annotation;

/**
 * Annotation for GraphQL scalar plugins.
 *
 * @Annotation
 */
class GraphQLEnum extends GraphQLAnnotationBase {

  /**
   * {@inheritdoc}
   */
  public $pluginType = GRAPHQL_ENUM_PLUGIN;

  /**
   * The typed data type of this type.
   *
   * @var string|null
   */
  public $type = NULL;

}
