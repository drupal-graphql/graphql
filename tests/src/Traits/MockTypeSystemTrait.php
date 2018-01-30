<?php

namespace Drupal\Tests\graphql\Traits;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginManager;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginManagerInterface;
use Drupal\KernelTests\KernelTestBase;
use Prophecy\Argument;

trait MockTypeSystemTrait {

  /**
   * @var
   */
  protected $typeSystemPluginManagerProphecies = [];

  /**
   * @var \Drupal\Component\Plugin\PluginInspectionInterface[][]
   */
  protected $typeSystemPlugins = [];

  /**
   * @var string[]
   */
  protected $typeSystemClassMap = [];

  public function typeSystemPluginManagerFactory($id) {
    return $this->typeSystemPluginManagerProphecies[$id]->reveal();
  }

  protected function registerTypeSystemPluginManagers(ContainerBuilder $container) {
    if ($this instanceof KernelTestBase) {
      $that = $this;

      foreach (array_keys($container->findTaggedServiceIds('graphql_plugin_manager')) as $id) {
        $definition = $container->getDefinition($id);
        $this->typeSystemClassMap[$id] = $definition->getArguments()[3];
        $this->typeSystemPlugins[$id] = [];

        $manager = $this->prophesize(TypeSystemPluginManagerInterface::class);

        $manager->getDefinitions()->will(function () use ($that, $id) {
          return array_map(function (PluginInspectionInterface $plugin) {
            return $plugin->getPluginDefinition();
          }, $that->typeSystemPlugins[$id]);
        });

        $manager->getDefinition(Argument::type('string'))->will(function ($args) use ($that, $id) {
          if (!isset($that->typeSystemPlugins[$id][$args[0]])) {
            throw new PluginNotFoundException($args[0]);
          }
          return $that->typeSystemPlugins[$id][$args[0]]->getPluginDefinition();
        });

        $manager->createInstance(Argument::type('string'), Argument::cetera())->will(function ($args) use ($that, $id) {
          if (!isset($that->typeSystemPlugins[$id][$args[0]])) {
            throw new PluginNotFoundException($args[0]);
          }
          return $that->typeSystemPlugins[$id][$args[0]];
        });

        $this->typeSystemPluginManagerProphecies[$id] = $manager;

        $new = $container->register('test.' . $id, TypeSystemPluginManager::class);
        $new->addTag('graphql_plugin_manager');
        $new->setFactory([$this, 'typeSystemPluginManagerFactory']);
        $new->addArgument($id);
      }
    }
  }

  public function addPlugin(TypeSystemPluginInterface $plugin) {
    foreach ($this->typeSystemClassMap as $id => $class) {
      if ($plugin instanceof $class) {
        $this->typeSystemPlugins[$id][$plugin->getPluginId()] = $plugin;
      }
    }
  }

}
