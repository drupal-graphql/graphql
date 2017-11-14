<?php

namespace Drupal\graphql\Annotation;

use Doctrine\Common\Annotations\AnnotationException;
use Drupal\Component\Annotation\Plugin;
use Drupal\Core\Cache\CacheBackendInterface;

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
   * @see graphql.module
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
   * The component description.
   *
   * @var string
   */
  public $description = '';

  /**
   * Weight for precedence calculations.
   *
   * If multiple components with the same name are available, the highest
   * weight wins.
   *
   * @var int
   */
  public $weight = 0;

  /**
   * The cache contexts for caching the type system definition in the schema.
   *
   * @var array
   */
  public $schema_cache_contexts = ['languages:language_interface'];

  /**
   * The cache tags for caching the type system definition in the schema.
   *
   * @var array
   */
  public $schema_cache_tags = [];

  /**
   * The cache max age for caching the type system definition in the schema.
   *
   * @var array
   */
  public $schema_cache_max_age = CacheBackendInterface::CACHE_PERMANENT;

  /**
   * The cache contexts for caching the response.
   *
   * @var array
   */
  public $response_cache_contexts = ['user'];

  /**
   * The cache tags for caching theresponse.
   *
   * @var array
   */
  public $response_cache_tags = [];

  /**
   * The cache max age for caching the response.
   *
   * @var array
   */
  public $response_cache_max_age = CacheBackendInterface::CACHE_PERMANENT;

}
