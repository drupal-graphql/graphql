<?php

declare(strict_types=1);

namespace Drupal\graphql\Annotation;

use Drupal\Component\Annotation\Doctrine\AnnotationException;
use Drupal\Component\Annotation\Plugin;

/**
 * Annotation for persisted query plugins.
 *
 * @Annotation
 * @codeCoverageIgnore
 */
class PersistedQuery extends Plugin {

  /**
   * The plugin ID.
   */
  public string $id;

  /**
   * The component label.
   */
  public string $label;

  /**
   * The component description.
   */
  public string $description = '';

  /**
   * PersistedQuery constructor.
   *
   * @param array $values
   *   The plugin annotation values.
   *
   * @throws \Drupal\Component\Annotation\Doctrine\AnnotationException
   *   In case of missing required annotation values.
   */
  public function __construct(array $values) {
    if (!array_key_exists('id', $values) || !$values['id']) {
      throw new AnnotationException('The graphql persisted query plugin is missing an "id" property.');
    }

    parent::__construct($values);
  }

}
