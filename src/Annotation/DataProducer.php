<?php

declare(strict_types=1);

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
   */
  public string $id;

  /**
   * The component name.
   */
  public string $name;

  /**
   * The component description.
   */
  public string $description = '';

  /**
   * DataProducer constructor.
   *
   * @param array $values
   *   The plugin annotation values.
   *
   * @throws \Doctrine\Common\Annotations\AnnotationException
   *   In case of missing required annotation values.
   */
  public function __construct(array $values) {
    if (!array_key_exists('id', $values) || !$values['id']) {
      throw new AnnotationException('The plugin is missing an "id" property.');
    }

    parent::__construct($values);
  }

}
