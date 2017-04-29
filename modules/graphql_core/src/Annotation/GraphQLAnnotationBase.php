<?php

namespace Drupal\graphql_core\Annotation;

use Drupal\Component\Annotation\Plugin;
use Drupal\graphql_core\GraphQLSchemaManagerInterface;

/**
 * Annotation for GraphQL input type plugins.
 *
 * @Annotation
 */
abstract class GraphQLAnnotationBase extends Plugin {

  /**
   * The plugin type.
   *
   * The type of component. Field, Interface, Type, Scalar ...
   *
   * @var string
   * @see graphql_core.module
   */
  public $pluginType = NULL;

  /**
   * {@inheritdoc}
   */
  public function getId() {
    if (!array_key_exists('id', $this->definition)) {
      return $this->definition['provider'] . '-' . $this->definition['name'];
    }
    return $this->definition['id'];
  }

  /**
   * The component name.
   *
   * @var string
   */
  public $name;

  /**
   * Weight for precedence calculations.
   *
   * If multiple components with the same name are available, the highest
   * weight wins.
   *
   * @var int
   */
  public $weight = 0;

}
