<?php

namespace Drupal\graphql\GraphQL\Execution;

use Drupal\graphql\Plugin\SchemaPluginInterface;

class ServerConfig extends \GraphQL\Server\ServerConfig {

  /**
   * @var \Drupal\graphql\Plugin\SchemaPluginInterface
   */
  protected $plugin;

  /**
   * @param \Drupal\graphql\Plugin\SchemaPluginInterface $schema
   *
   * @return \Drupal\graphql\GraphQL\Execution\ServerConfig
   */
  public static function createForSchema(SchemaPluginInterface $schema) {
    $config = new static();
    $config->plugin = $schema;

    $config->setContext($schema->getContext());
    $config->setRootValue($schema->getRootValue());
    $config->setSchema($schema->getSchema());
    $config->setErrorFormatter($schema->getErrorFormatter());
    $config->setErrorsHandler($schema->getErrorHandler());

    return $config;
  }

  /**
   * @return \Drupal\graphql\Plugin\SchemaPluginInterface
   */
  public function getPlugin() {
    return $this->plugin;
  }

}