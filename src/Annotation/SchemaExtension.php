<?php

declare(strict_types=1);

namespace Drupal\graphql\Annotation;

use Drupal\Component\Annotation\Doctrine\AnnotationException;
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
   */
  public string $id;

  /**
   * The plugin name.
   */
  public string $name;

  /**
   * The plugin description.
   */
  public string $description = '';

  /**
   * The id of the schema plugin to extend.
   */
  public string $schema;

  /**
   * The priority of the extension.
   *
   * Plugins with higher priority will be executed first.
   */
  public int $priority = 0;

  /**
   * SchemaExtension constructor.
   *
   * @param array $values
   *   The plugin annotation values.
   *
   * @throws \Drupal\Component\Annotation\Doctrine\AnnotationException
   *   In case of missing required values.
   */
  public function __construct(array $values) {
    if (!array_key_exists('id', $values) || !$values['id']) {
      throw new AnnotationException('The graphql schema extension plugin is missing an "id" property.');
    }

    parent::__construct($values);
  }

}
