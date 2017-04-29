<?php

namespace Drupal\graphql_core\Annotation;

/**
 * Annotation for GraphQL field plugins.
 *
 * @Annotation
 */
class GraphQLField extends GraphQLMutation {

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

}
