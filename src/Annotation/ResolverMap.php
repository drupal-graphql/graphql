<?php

namespace Drupal\graphql\Annotation;

use Doctrine\Common\Annotations\AnnotationException;
use Drupal\Component\Annotation\Plugin;

/**
 * Defines a resolver map annotation object.
 *
 * Plugin Namespace: Plugin\ResolverMap
 *
 * @see \Drupal\graphql\Plugin\ResolverMapPluginInterface
 * @see \Drupal\graphql\Plugin\ResolverMapPluginManager
 * @see plugin_api
 *
 * @Annotation
 */
class ResolverMap extends Plugin {

  /**
   * The plugin ID.
   *
   * @var string
   */
  public $id;

  /**
   * The schema ID.
   *
   * @var string
   */
  public $schema;

  /**
   * ResolverMap constructor.
   *
   * @param $values
   *   The plugin annotation values.
   *
   * @throws \Doctrine\Common\Annotations\AnnotationException
   *   In case of missing required annotation values.
   */
  public function __construct($values) {
    if (!array_key_exists('schema', $values) || !$values['schema']) {
      throw new AnnotationException('The plugin is missing an "schema" property.');
    }

    parent::__construct($values);
  }

}
