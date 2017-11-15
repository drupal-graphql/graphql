<?php

namespace Drupal\graphql\Annotation;

use Doctrine\Common\Annotations\AnnotationException;
use Drupal\Component\Annotation\Plugin;
/**
 * Annotation for GraphQL schema plugins.
 *
 * @Annotation
 */
class GraphQLSchema extends Plugin {

  /**
   * {@inheritdoc}
   *
   * Enforce explicit id's on GraphQL plugin annotations.
   */
  public function __construct($values) {
    if (!array_key_exists('id', $values) || !$values['id']) {
      throw new AnnotationException('GraphQL schema is missing an "id" property.');
    }

    if (!array_key_exists('path', $values) || !$values['path']) {
      throw new AnnotationException('GraphQL schema is missing an "path" property.');
    }

    parent::__construct($values);
  }

  /**
   * The schema name.
   *
   * @var string
   */
  public $name;

  /**
   * The schema path.
   *
   * @var string
   */
  public $path;

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
