<?php

namespace Drupal\graphql\GraphQL\Schema;

use Drupal\graphql\Plugin\GraphQL\SchemaPluginInterface;
use Youshido\GraphQL\Config\Schema\SchemaConfig;
use Youshido\GraphQL\Schema\AbstractSchema;

class Schema extends AbstractSchema {

  /**
   * The corresponding plugin for this schema.
   *
   * @var \Drupal\graphql\Plugin\GraphQL\SchemaPluginInterface
   */
  protected $plugin;

  /**
   * Schema constructor.
   *
   * @param \Drupal\graphql\Plugin\GraphQL\SchemaPluginInterface $plugin
   *   The corresponding plugin instance for this schema.
   * @param array $config
   *   The schema configuration array.
   */
  public function __construct(SchemaPluginInterface $plugin, array $config = []) {
    parent::__construct($config);
    $this->plugin = $plugin;
  }

  /**
   * Retrieves the schema's plugin instance.
   *
   * @return \Drupal\graphql\Plugin\GraphQL\SchemaPluginInterface
   *   The schema plugin instance.
   */
  public function getSchemaPlugin() {
    return $this->plugin;
  }

  /**
   * {@inheritdoc}
   */
  public function build(SchemaConfig $config) {
    // Nothing to do here.
  }

}
