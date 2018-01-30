<?php

namespace Drupal\Tests\graphql\Traits;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\graphql\Plugin\GraphQL\SchemaPluginManager;
use Drupal\graphql\Plugin\GraphQL\Schemas\SchemaPluginBase;
use Drupal\KernelTests\KernelTestBase;
use Prophecy\Argument;

trait MockSchemaTrait {

  abstract function getSchemaDefinitions();

  /**
   * @var \Prophecy\Prophecy\ObjectProphecy
   */
  protected $schemaManagerProphecy;

  public function schemaManagerFactory() {
    return $this->schemaManagerProphecy->reveal();
  }

  protected function registerSchemaPluginManager(ContainerBuilder $container) {
    if ($this instanceof KernelTestBase) {
      $this->schemaManagerProphecy = $this->prophesize(SchemaPluginManager::class);

      $that = $this;

      $this->schemaManagerProphecy->getDefinitions()->will(function () use ($that) {
        return $that->getSchemaDefinitions();
      });

      $this->schemaManagerProphecy->getDefinition(Argument::type('string'))->will(function ($args) use ($that) {
        return $that->getSchemaDefinitions()[$args[0]];
      });

      $this->schemaManagerProphecy->createInstance(Argument::type('string'), Argument::cetera())->will(function ($args) use ($that) {
        return $that->getMockForAbstractClass(SchemaPluginBase::class, [
          [],
          'default',
          $that->getSchemaDefinitions()[$args[0]],
          $that->container->get('graphql.plugin_manager_aggregator'),
        ]);
      });

      $definition = $container->getDefinition('plugin.manager.graphql.schema');
      $definition->setClass(get_class($this->schemaManagerProphecy->reveal()));
      $definition->setFactory([$this, 'schemaManagerFactory']);
    }
  }

}
