<?php

namespace Drupal\graphql_core\Annotation;

use Doctrine\Common\Annotations\AnnotationException;
use Drupal\Component\Annotation\Plugin;

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
   *
   * Enforce explicit id's on GraphQL plugin annotations.
   */
  public function __construct($values) {
    if (!array_key_exists('id', $values) || !$values['id']) {
      throw new AnnotationException('GraphQL plugin is missing an "id" property.');
    }
    parent::__construct($values);
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
