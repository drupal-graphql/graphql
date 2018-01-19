<?php

namespace Drupal\graphql\GraphQL\Schema;

use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface;
use Youshido\GraphQL\Config\Schema\SchemaConfig;
use Youshido\GraphQL\Schema\AbstractSchema;

class Schema extends AbstractSchema {

  /**
   * The corresponding plugin for this schema.
   *
   * @var \Drupal\graphql\Plugin\GraphQL\SchemaPluginInterface
   */
  protected $builder;

  /**
   * Schema constructor.
   *
   * @param \Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface $builder
   *   The schema builder.
   * @param array $config
   *   The schema configuration array.
   */
  public function __construct(PluggableSchemaBuilderInterface $builder, array $config = []) {
    parent::__construct($config);
    $this->builder = $builder;
  }

  /**
   * Retrieves the schema's plugin instance.
   *
   * @return \Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface
   *   The schema builder.
   */
  public function getSchemaBuilder() {
    return $this->builder;
  }

  /**
   * {@inheritdoc}
   */
  public function build(SchemaConfig $config) {
    // Nothing to do here.
  }

}
