<?php

namespace Drupal\graphql\Annotation;

use Doctrine\Common\Annotations\AnnotationException;
use Drupal\Component\Annotation\Plugin;

/**
 * Annotation for data producer plugins.
 *
 * @Annotation
 * @codeCoverageIgnore
 */
class DataProducer extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The component name.
   *
   * @var string
   */
  public $name;

  /**
   * The component description.
   *
   * @var string
   */
  public $description = '';

  /**
   * DataProducer constructor.
   *
   * @param $values
   *   The plugin annotation values.
   *
   * @throws \Doctrine\Common\Annotations\AnnotationException
   *   In case of missing required annotation values.
   */
  public function __construct($values) {
    if (!array_key_exists('id', $values) || !$values['id']) {
      throw new AnnotationException('The plugin is missing an "id" property.');
    }

    parent::__construct($values);
  }

}
