<?php

namespace Drupal\graphql\Plugin\GraphQL;

use Symfony\Component\DependencyInjection\ContainerInterface;

interface SchemaBuilderInterface {

  /**
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   * @param string $pluginId
   * @param array $pluginDefinition
   * @return mixed
   */
  public static function createInstance(ContainerInterface $container, $pluginId, array $pluginDefinition);

  /**
   * @return \Youshido\GraphQL\Config\Schema\SchemaConfig
   */
  public function getSchemaConfig();

}
