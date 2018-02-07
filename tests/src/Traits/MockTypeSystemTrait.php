<?php

namespace Drupal\Tests\graphql\Traits;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\graphql\Annotation\GraphQLEnum;
use Drupal\graphql\Annotation\GraphQLField;
use Drupal\graphql\Annotation\GraphQLInputType;
use Drupal\graphql\Annotation\GraphQLInterface;
use Drupal\graphql\Annotation\GraphQLMutation;
use Drupal\graphql\Annotation\GraphQLType;
use Drupal\graphql\Annotation\GraphQLUnionType;
use Drupal\graphql\Plugin\GraphQL\Enums\EnumPluginBase;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Drupal\graphql\Plugin\GraphQL\InputTypes\InputTypePluginBase;
use Drupal\graphql\Plugin\GraphQL\Interfaces\InterfacePluginBase;
use Drupal\graphql\Plugin\GraphQL\Mutations\MutationPluginBase;
use Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginManager;
use Drupal\graphql\Plugin\GraphQL\Unions\UnionTypePluginBase;
use Drupal\KernelTests\KernelTestBase;

/**
 * Trait for mocking GraphQL type system plugins.
 */
trait MockTypeSystemTrait {

  /**
   * The mocked type system plugin managers.
   *
   * @var TypeSystemPluginManager[]
   */
  protected $typeSystemPluginManagers = [];

  /**
   * The list of mocked type system plugins.
   *
   * @var \Drupal\Component\Plugin\PluginInspectionInterface[][]
   */
  protected $typeSystemPlugins = [];

  /**
   * Maps type system manager id's to required plugin interfaces.
   *
   * @var string[]
   */
  protected $typeSystemClassMap = [];

  /**
   * Container factory method to retrieve a type system plugin manager.
   *
   * @param $id
   *   The manager id.
   *
   * @return \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginManager
   *   The plugin manager instance.
   */
  public function typeSystemPluginManagerFactory($id) {
    return $this->typeSystemPluginManagers[$id];
  }

  /**
   * Register the mocked plugin managers during container build.
   *
   * Injects the mocked schema managers into the drupal container. Has to be
   * invoked during the KernelTest's register callback.
   *
   * @param \Drupal\Core\DependencyInjection\ContainerBuilder $container
   *   The container builder instance.
   */
  protected function registerTypeSystemPluginManagers(ContainerBuilder $container) {
    assert($this instanceof KernelTestBase, 'MockSchemaTrait has to be used in a KernelTest.');

    foreach (array_keys($container->findTaggedServiceIds('graphql_plugin_manager')) as $id) {
      $definition = $container->getDefinition($id);
      $this->typeSystemClassMap[$id] = $definition->getArguments()[3];
      $this->typeSystemPlugins[$id] = [];

      $manager = $this->getMockBuilder(TypeSystemPluginManager::class)
        ->disableOriginalConstructor()
        ->setMethods([
          'getDefinitions',
          'hasDefinition',
          'getDefinition',
          'createInstance',
        ])->getMock();

      $manager
        ->expects(static::any())
        ->method('getDefinitions')
        ->willReturnCallback(function () use ($id) {
          return array_map(function (PluginInspectionInterface $plugin) {
            return $plugin->getPluginDefinition();
          }, $this->typeSystemPlugins[$id]);
        });

      $manager
        ->expects(static::any())
        ->method('hasDefinition')
        ->with(static::anything())
        ->willReturnCallback(function ($pluginId) use ($id) {
          return isset($this->typeSystemPlugins[$id][$pluginId]);
        });

      $manager
        ->expects(static::any())
        ->method('getDefinition')
        ->with(static::anything())
        ->willReturnCallback(function ($pluginId) use ($id) {
          return $this->typeSystemPlugins[$id][$pluginId]->getPluginDefinition();
        });


      $manager
        ->expects(static::any())
        ->method('createInstance')
        ->with(static::anything(), static::anything())
        ->willReturnCallback(function ($pluginId) use ($id) {
          return $this->typeSystemPlugins[$id][$pluginId];
        });

      $this->typeSystemPluginManagers[$id] = $manager;

      $new = $container->register('test.' . $id, TypeSystemPluginManager::class);
      $new->addTag('graphql_plugin_manager');
      $new->setFactory([$this, 'typeSystemPluginManagerFactory']);
      $new->addArgument($id);
    }
  }

  /**
   * Get a plugin definition.
   *
   * Merges plugin definition with the default values for a specified
   * annotation class.
   *
   * @param string $annotationClass
   *   The plugin annotation class name.
   * @param array $definition
   *   The definition values.
   *
   * @return array
   *   The complete plugin definition.
   *
   * @internal
   */
  protected function getTypeSystemPluginDefinition($annotationClass, array $definition) {
    return (new $annotationClass($definition))->get();
  }

  /**
   * Add a new plugin to the GraphQL type system.
   *
   * @param \Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface $plugin
   *   The plugin to add.
   *
   * @internal
   */
  protected function addTypeSystemPlugin(TypeSystemPluginInterface $plugin) {
    foreach ($this->typeSystemClassMap as $id => $class) {
      if ($plugin instanceof $class) {
        $this->typeSystemPlugins[$id][$plugin->getPluginId()] = $plugin;
      }
    }
  }

  /**
   * Turn a value into a result promise.
   *
   * @param mixed $value
   *   The return value. Can also be a value callback.
   *
   * @return \PHPUnit_Framework_MockObject_Stub_ReturnCallback
   *   The return callback promise.
   */
  protected function toPromise($value) {
    return $this->returnCallback(is_callable($value) ? $value : function () use ($value) {
      yield $value;
    });
  }

  /**
   * Turn a value into a bound result promise.
   *
   * @param mixed $value
   *   The return value. Can also be a value callback.
   * @param mixed $scope
   *   The resolver's bound object and class scope.
   *
   * @return \PHPUnit_Framework_MockObject_Stub_ReturnCallback
   *   The return callback promise.
   */
  protected function toBoundPromise($value, $scope) {
    return $this->toPromise(is_callable($value) ? \Closure::bind($value, $scope, $scope) : $value);
  }

  /**
   * Mock a GraphQL field.
   *
   * @param string $id
   *   The field id.
   * @param array $definition
   *   The plugin definition. Will be merged with the field defaults.
   * @param mixed|null $result
   *   A result for this field. Can be a value or a callback. If omitted, no
   *   resolve method mock will be attached.
   *
   * @return \PHPUnit_Framework_MockObject_MockObject
   *   The field mock object.
   */
  protected function mockField($id, $definition, $result = NULL) {
    $definition = $this->getTypeSystemPluginDefinition(
     GraphQLField::class,
     $definition + ['id' => $id]
    );

    $field = $this->getMockBuilder(FieldPluginBase::class)
      ->setConstructorArgs([[], $id, $definition])
      ->setMethods([
        'resolveValues',
      ])->getMock();

    if (isset($result)) {
      $field
        ->expects(static::any())
        ->method('resolveValues')
        ->with($this->anything(), $this->anything(), $this->anything())
        ->will($this->toBoundPromise($result, $field));
    }

    $this->addTypeSystemPlugin($field);

    return $field;
  }

  /**
   * Mock a GraphQL type.
   *
   * @param string $id
   *   The type id.
   * @param array $definition
   *   The plugin definition. Will be merged with the type defaults.
   * @param mixed|null $applies
   *   A result for the types "applies" method. Defaults to `TRUE`.
   *
   * @return \PHPUnit_Framework_MockObject_MockObject
   *   The type mock object.
   */
  protected function mockType($id, array $definition, $applies = TRUE) {
    $definition = $this->getTypeSystemPluginDefinition(
      GraphQLType::class,
      $definition + ['id' => $id]
    );

    $type = $this->getMockBuilder(TypePluginBase::class)
      ->setConstructorArgs([[], $id, $definition])
      ->setMethods([
        'applies',
      ])->getMock();

    $type
      ->expects(static::any())
      ->method('applies')
      ->with($this->anything(), $this->anything())
      ->will($this->toBoundPromise($applies, $type));

    $this->addTypeSystemPlugin($type);

    return $type;
  }

  /**
   * Mock a GraphQL input type.
   *
   * @param string $id
   *   The input type id.
   * @param array $definition
   *   The plugin definition. Will be merged with the input type defaults.
   *
   * @return \PHPUnit_Framework_MockObject_MockObject
   *   The input type mock object.
   */
  protected function mockInputType($id, array $definition) {
    $definition = $this->getTypeSystemPluginDefinition(
      GraphQLInputType::class,
      $definition + ['id' => $id]
    );

    $input = $this->getMockForAbstractClass(
      InputTypePluginBase::class, [
        [],
        $id,
        $definition,
      ]
    );

    $this->addTypeSystemPlugin($input);

    return $input;
  }

  /**
   * Mock a GraphQL mutation.
   *
   * @param string $id
   *   The mutation id.
   * @param array $definition
   *   The plugin definition. Will be merged with the mutation defaults.
   * @param mixed|null $result
   *   A result for this mutation. Can be a value or a callback. If omitted, no
   *   resolve method mock will be attached.
   *
   * @return \PHPUnit_Framework_MockObject_MockObject
   *   The mutation mock object.
   */
  protected function mockMutation($id, array $definition, $result = NULL) {
    $definition = $this->getTypeSystemPluginDefinition(
      GraphQLMutation::class,
      $definition + ['id' => $id]
    );

    $mutation = $this->getMockBuilder(MutationPluginBase::class)
      ->setConstructorArgs([[], $id, $definition])
      ->setMethods([
        'resolve',
      ])->getMock();

    if (isset($result)) {
      $mutation
        ->expects(static::any())
        ->method('resolve')
        ->with($this->anything(), $this->anything(), $this->anything())
        ->will($this->toBoundPromise($result, $mutation));
    }

    $this->addTypeSystemPlugin($mutation);

    return $mutation;
  }

  /**
   * Mock a GraphQL interface.
   *
   * @param string $id
   *   The interface id.
   * @param array $definition
   *   The plugin definition. Will be merged with the interface defaults.
   *
   * @return \PHPUnit_Framework_MockObject_MockObject
   *   The interface mock object.
   */
  protected function mockInterface($id, array $definition) {
    $definition = $this->getTypeSystemPluginDefinition(
      GraphQLInterface::class,
      $definition + ['id' => $id]
    );

    $interface = $this->getMockForAbstractClass(InterfacePluginBase::class, [
      [],
      $id,
      $definition,
    ]);

    $this->addTypeSystemPlugin($interface);

    return $interface;
  }

  /**
   * Mock a GraphQL union.
   *
   * @param string $id
   *   The union id.
   * @param array $definition
   *   The plugin definition. Will be merged with the union defaults.
   *
   * @return \PHPUnit_Framework_MockObject_MockObject
   *   The union mock object.
   */
  protected function mockUnion($id, array $definition) {
    $definition = $this->getTypeSystemPluginDefinition(
      GraphQLUnionType::class,
      $definition + ['id' => $id]
    );

    $union = $this->getMockForAbstractClass(UnionTypePluginBase::class, [
      [],
      $id,
      $definition,
    ]);

    $this->addTypeSystemPlugin($union);

    return $union;
  }

  /**
   * Mock a GraphQL enum.
   *
   * @param string $id
   *   The enum id.
   * @param array $definition
   *   The plugin definition. Will be merged with the enum defaults.
   * @param mixed $values
   *   The array enum values. Can also be a value callback.
   *
   * @return \PHPUnit_Framework_MockObject_MockObject
   *   The enum mock object.
   */
  protected function mockEnum($id, array $definition, $values = []) {
    $definition = $this->getTypeSystemPluginDefinition(
      GraphQLEnum::class,
      $definition + ['id' => $id]
    );

    $enum = $this->getMockBuilder(EnumPluginBase::class)
      ->setConstructorArgs([[], $id, $definition])
      ->setMethods([
        'buildValues',
      ])->getMock();

    $enum
      ->expects(static::any())
      ->method('buildValues')
      ->with($this->anything())
      ->will($this->toBoundPromise($values, $enum));

    $this->addTypeSystemPlugin($enum);

    return $enum;
  }

}
