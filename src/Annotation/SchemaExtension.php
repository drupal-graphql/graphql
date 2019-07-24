<?php

namespace Drupal\graphql\Annotation;

use Doctrine\Common\Annotations\AnnotationException;
use Drupal\Component\Annotation\Plugin;

/**
 * Annotation for GraphQL schema extension plugins.
 *
 * @Annotation
 * @codeCoverageIgnore
 */
class SchemaExtension extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The plugin name.
   *
   * @var string
   */
  public $name;

  /**
   * The plugin description.
   *
   * @var string
   */
  public $description = '';

  /**
   * The id of the schema plugin to extend.
   *
   * @var string
   */
  public $schema;

  /**
   * SchemaExtension constructor.
   *
   * @param mixed $values
   *   The plugin annotation values.
   *
   * @throws \Doctrine\Common\Annotations\AnnotationException
   *   In case of missing required values.
   */
  public function __construct($values) {
    if (!array_key_exists('id', $values) || !$values['id']) {
      throw new AnnotationException('The plugin is missing an "id" property.');
    }

    parent::__construct($values);
  }

}
