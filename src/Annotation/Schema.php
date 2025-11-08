<?php

declare(strict_types=1);

namespace Drupal\graphql\Annotation;

use Drupal\Component\Annotation\Doctrine\AnnotationException;
use Drupal\Component\Annotation\Plugin;

/**
 * Annotation for GraphQL schema plugins.
 *
 * @Annotation
 * @codeCoverageIgnore
 */
class Schema extends Plugin {

  /**
   * The plugin ID.
   */
  public string $id;

  /**
   * The schema name.
   */
  public string $name;

  /**
   * The component description.
   */
  public string $description = '';

  /**
   * Schema constructor.
   *
   * @param array $values
   *   The plugin annotation values.
   *
   * @throws \Drupal\Component\Annotation\Doctrine\AnnotationException
   *   In case of missing required values.
   */
  public function __construct(array $values) {
    if (!array_key_exists('id', $values) || !$values['id']) {
      throw new AnnotationException('The graphql schema plugin is missing an "id" property.');
    }

    parent::__construct($values);
  }

}
