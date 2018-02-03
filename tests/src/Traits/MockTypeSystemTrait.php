<?php

namespace Drupal\Tests\graphql\Traits;

use Drupal\Component\Plugin\Exception\PluginNotFoundException;
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
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginManagerInterface;
use Drupal\graphql\Plugin\GraphQL\Unions\UnionTypePluginBase;
use Drupal\KernelTests\KernelTestBase;
use Prophecy\Argument;

trait MockTypeSystemTrait {

  /**
   * @var \Prophecy\Prophecy\ObjectProphecy[]
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

        $manager->hasDefinition(Argument::type('string'))->will(function ($args) use ($that, $id) {
          return isset($that->typeSystemPlugins[$id][$args[0]]);
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

  protected function getTypeSystemPluginDefaults($annotationClass, $definition) {
    return (new $annotationClass($definition))->get();
  }

  protected function addTypeSystemPlugin(TypeSystemPluginInterface $plugin) {
    foreach ($this->typeSystemClassMap as $id => $class) {
      if ($plugin instanceof $class) {
        $this->typeSystemPlugins[$id][$plugin->getPluginId()] = $plugin;
      }
    }
  }

  protected function toPromise($value) {
    return $this->returnCallback(is_callable($value) ? $value : function () use ($value) {
      yield $value;
    });
  }

  /**
   * Mock a GraphQL field.
   *
   * @param $id
   *   The field id.
   * @param $definition
   *   The plugin definition. Will be merged with the field defaults.
   * @param mixed|null $result
   *   A result for this field. Can be a value or a callback. If omitted, no
   *   resolve method mock will be attached.
   *
   * @return \PHPUnit_Framework_MockObject_MockObject
   *   The field mock object.
   */
  protected function mockField($id, $definition, $result = NULL) {
    $definition = $this->getTypeSystemPluginDefaults(
     GraphQLField::class,
     $definition + ['id' => $id]
    );

    $field = $this->getMockBuilder(FieldPluginBase::class)
      ->setConstructorArgs([[], $id, $definition])
      ->setMethods([
        'resolveValues',
      ])->getMock();

    if ($result) {
      $field
        ->expects(static::any())
        ->method('resolveValues')
        ->with($this->anything(), $this->anything(), $this->anything())
        ->will($this->toPromise($result));
    }

    $this->addTypeSystemPlugin($field);

    return $field;
  }

  protected function mockType($id, $definition, $applies = TRUE) {
    $definition = $this->getTypeSystemPluginDefaults(
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
      ->will($this->toPromise($applies));

    $this->addTypeSystemPlugin($type);

    return $type;
  }

  protected function mockInputType($id, $definition) {
    $definition = $this->getTypeSystemPluginDefaults(
      GraphQLInputType::class,
      $definition + ['id' => $id]
    );

    $input = $this->getMockForAbstractClass(InputTypePluginBase::class, [[], $id, $definition]);

    $this->addTypeSystemPlugin($input);

    return $input;
  }

  protected function mockMutation($id, $definition, $result = NULL) {
    $definition = $this->getTypeSystemPluginDefaults(
      GraphQLMutation::class,
      $definition + ['id' => $id]
    );

    $mutation = $this->getMockBuilder(MutationPluginBase::class)
      ->setConstructorArgs([[], $id, $definition])
      ->setMethods([
        'resolve',
      ])->getMock();

    $mutation
      ->expects(static::any())
      ->method('resolve')
      ->with($this->anything(), $this->anything(), $this->anything())
      ->will($this->toPromise($result));

    $this->addTypeSystemPlugin($mutation);

    return $mutation;
  }

  protected function mockInterface($id, $definition) {
    $definition = $this->getTypeSystemPluginDefaults(
      GraphQLInterface::class,
      $definition + ['id' => $id]
    );

    $interface = $this->getMockForAbstractClass(InterfacePluginBase::class, [[], $id, $definition]);

    $this->addTypeSystemPlugin($interface);

    return $interface;
  }

  protected function mockUnion($id, $definition) {
    $definition = $this->getTypeSystemPluginDefaults(
      GraphQLUnionType::class,
      $definition + ['id' => $id]
    );


    $union = $this->getMockForAbstractClass(UnionTypePluginBase::class, [[], $id, $definition]);

    $this->addTypeSystemPlugin($union);

    return $union;
  }

  protected function mockEnum($id, $definition, $values = []) {
    $definition = $this->getTypeSystemPluginDefaults(
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
      ->will($this->toPromise($values));

    $this->addTypeSystemPlugin($enum);

    return $enum;
  }

}
