<?php

namespace Drupal\Tests\graphql\Traits;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\graphql\Plugin\GraphQL\SchemaPluginManager;
use Drupal\graphql\Plugin\GraphQL\Schemas\SchemaPluginBase;
use Drupal\KernelTests\KernelTestBase;

/**
 * Inject mocked schema definitions.
 */
trait MockSchemaTrait {

  /**
   * Get the schema plugin definitions to mock.
   *
   * @see \Drupal\graphql\Annotation\GraphQLSchema
   *
   * @return array
   *   The schema plugin definitino array.
   */
  abstract protected function getSchemaDefinitions();

  /**
   * The schema manager mock.
   *
   * @var SchemaPluginManager
   */
  protected $schemaManager;

  /**
   * Factory method for the schema manager.
   *
   * @return \Drupal\graphql\Plugin\GraphQL\SchemaPluginManager
   *   The mocked schema manager instance.
   *
   * @internal
   */
  public function schemaManagerFactory() {
    return $this->schemaManager;
  }

  /**
   * Register the mocked plugin manager during container build.
   *
   * Injects the mocked schema manager into the drupal container. Has to be
   * invoked during the KernelTest's register callback.
   *
   * @param \Drupal\Core\DependencyInjection\ContainerBuilder $container
   *   The container builder instance.
   */
  protected function registerSchemaPluginManager(ContainerBuilder $container) {
    assert($this instanceof KernelTestBase, 'MockSchemaTrait has to be used in a KernelTest.');

    $schemaManager = $this->getMockBuilder(SchemaPluginManager::class)
      ->disableOriginalConstructor()
      ->setMethods([
        'getDefinitions',
        'getDefinition',
        'createInstance',
      ])->getMock();

    $schemaManager
      ->expects(static::any())
      ->method('getDefinitions')
      ->willReturnCallback(function () {
        return $this->getSchemaDefinitions();
      });

    $schemaManager
      ->expects(static::any())
      ->method('getDefinition')
      ->with(static::anything())
      ->willReturnCallback(function ($id) {
        return $this->getSchemaDefinitions()[$id];
      });

    $schemaManager
      ->expects(static::any())
      ->method('createInstance')
      ->with(static::anything(), static::anything())
      ->willReturnCallback(function ($id) {
        return $this->mockSchema($id);
      });

    $this->schemaManager = $schemaManager;

    $definition = $container->getDefinition('plugin.manager.graphql.schema');
    $definition->setClass(get_class($this->schemaManager));
    $definition->setFactory([$this, 'schemaManagerFactory']);
  }

  /**
   * Mock a schema instance.
   *
   * @param string $id
   *   The schema id.
   *
   * @return \Drupal\graphql\Plugin\GraphQL\Schemas\SchemaPluginBase
   *   The schema plugin mock.
   */
  protected function mockSchema($id) {
    return $this->getMockForAbstractClass(SchemaPluginBase::class, [
      [],
      $id,
      $this->getSchemaDefinitions()[$id],
      $this->container->get('graphql.plugin_manager_aggregator'),
    ]);
  }

}
