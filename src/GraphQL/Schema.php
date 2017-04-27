<?php

namespace Drupal\graphql\GraphQL;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\graphql\GraphQL\Type\AbstractInterfaceType;
use Drupal\graphql\GraphQL\Type\AbstractObjectType;
use Youshido\GraphQL\Config\Schema\SchemaConfig;
use Youshido\GraphQL\Introspection\Traits\TypeCollectorTrait;
use Youshido\GraphQL\Schema\AbstractSchema;
use Youshido\GraphQL\Type\InputObject\AbstractInputObjectType;

class Schema extends AbstractSchema implements CacheableDependencyInterface {

  use TypeCollectorTrait;

  /**
   * The cache metadata containing the caching information of the whole schema.
   *
   * @var \Drupal\Core\Cache\CacheableMetadata
   */
  protected $metadata;

  /**
   * All types that exist in the schema.
   *
   * @var \Youshido\GraphQL\Type\TypeInterface[]
   */
  protected $types;

  /**
   * {@inheritdoc}
   */
  public function build(SchemaConfig $config) {
    // Not needed in most cases as implementing modules are most likely going to
    // use the add methods to incrementally build the schema.
  }

  /**
   * {@inheritdoc}
   */
  public function addQueryField($field, $fieldInfo = NULL) {
    // Invalidate the cache metadata container.
    unset($this->metadata, $this->types);

    parent::addQueryField($field, $fieldInfo);
  }

  /**
   * {@inheritdoc}
   */
  public function addMutationField($field, $fieldInfo = NULL) {
    // Invalidate the cache metadata container.
    unset($this->metadata, $this->types);

    parent::addMutationField($field, $fieldInfo);
  }

  /**
   * @return \Youshido\GraphQL\Type\TypeInterface[]
   */
  protected function getAllRegisteredTypes() {
    if (isset($this->types)) {
      return $this->types;
    }

    $this->types = [];
    $this->collectTypes($this->getQueryType());

    if ($this->getMutationType()->hasFields()) {
      $this->collectTypes($this->getMutationType());
    }

    foreach ($this->getTypesList()->getTypes() as $type) {
      $this->collectTypes($type);
    }

    return $this->types;
  }

  /**
   * @param \Drupal\Core\Cache\CacheableMetadata $metadata
   *
   * @return $this
   */
  public function setCacheMetadata(CacheableMetadata $metadata) {
    $this->metadata = $metadata;
    return $this;
  }

  /**
   * @return \Drupal\Core\Cache\CacheableMetadata
   */
  public function getCacheMetadata() {
    if (isset($this->metadata)) {
      return $this->metadata;
    }

    $this->metadata = new CacheableMetadata();
    $this->metadata->setCacheMaxAge(Cache::PERMANENT);

    foreach ($this->getAllRegisteredTypes() as $type) {
      if ($type instanceof CacheableDependencyInterface) {
        $this->metadata->addCacheableDependency($type);

        if ($type instanceof AbstractObjectType || $type instanceof AbstractInputObjectType || $type instanceof AbstractInterfaceType) {
          foreach ($type->getFields() as $field) {
            $this->metadata->addCacheableDependency($field);
          }
        }
      }
    }

    return $this->metadata;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return $this->getCacheMetadata()->getCacheContexts();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    return $this->getCacheMetadata()->getCacheTags();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return $this->getCacheMetadata()->getCacheMaxAge();
  }
}
