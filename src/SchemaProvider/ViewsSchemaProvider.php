<?php

/**
 * @file
 * Contains \Drupal\graphql\SchemaProvider\ViewsSchemaProvider.
 */

namespace Drupal\graphql\SchemaProvider;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\TypedData\TypedDataManager;
use Drupal\graphql\TypeResolverInterface;
use Drupal\graphql\Utility\String;
use Drupal\views\ViewEntityInterface;
use Fubhy\GraphQL\Language\Node;
use Fubhy\GraphQL\Type\Definition\Types\ListModifier;
use Fubhy\GraphQL\Type\Definition\Types\NonNullModifier;
use Fubhy\GraphQL\Type\Definition\Types\ObjectType;
use Fubhy\GraphQL\Type\Definition\Types\Type;

/**
 * Generates a GraphQL Schema for views.
 */
class ViewsSchemaProvider extends SchemaProviderBase {
  /**
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * The type resolver service.
   *
   * @var \Drupal\graphql\TypeResolverInterface
   */
  protected $typeResolver;

  /**
   * The typed data manager service.
   *
   * @var \Drupal\Core\TypedData\TypedDataManager
   */
  protected $typedDataManager;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a ViewsSchemaProvider object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager service.
   * @param TypedDataManager $typed_data_manager
   *   The typed data manager service.
   * @param \Drupal\graphql\TypeResolverInterface $type_resolver
   *   The base type resolver service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   */
  public function __construct(EntityManagerInterface $entity_manager, TypedDataManager $typed_data_manager, TypeResolverInterface $type_resolver, ModuleHandlerInterface $module_handler) {
    $this->entityManager = $entity_manager;
    $this->typeResolver = $type_resolver;
    $this->typedDataManager = $typed_data_manager;
    $this->moduleHandler = $module_handler;
  }

  /**
   * @return array
   */
  protected function getDataTableMap() {
    $map = [];
    foreach ($this->entityManager->getDefinitions() as $entity_type_id => $entity_type) {
      if ($data_table = $entity_type->getDataTable()) {
        $map[$data_table] = $entity_type->id();
      }
    }

    return $map;
  }

  /**
   * {@inheritdoc}
   */
  public function getQuerySchema() {
    // Do not attempt to generate the schema if the views module is not enabled.
    if (!$this->moduleHandler->moduleExists('views')) {
      return [];
    }

    $fields = [];

    $map = $this->getDataTableMap();
    /** @var \Drupal\views\ViewEntityInterface $view */
    foreach ($this->entityManager->getStorage('view')->loadMultiple() as $view) {
      $base_table = $view->get('base_table');
      if (!isset($map[$base_table])) {
        continue;
      }

      $displays = [];
      foreach ($view->get('display') as $display_id => $display) {
        if ($display['display_plugin'] !== 'graphql') {
          continue;
        }

        $executable = $view->getExecutable();
        $executable->setDisplay($display_id);

        $filters = array_reduce(array_filter($executable->getDisplay()->getOption('filters'), function (array $filter) {
          return !empty($filter['exposed']);
        }), function (array $carry, array $filter) {
          $type = $filter['expose']['required'] ? new NonNullModifier(Type::stringType()) : Type::stringType();
          $type = $filter['expose']['multiple'] ? new ListModifier($type) : $type;
          $type = $filter['expose']['required'] ? new NonNullModifier($type) : $type;

          $carry[$filter['expose']['identifier']] = [
            'type' => $type,
          ];

          return $carry;
        }, []);

        $definition = $this->typedDataManager->createDataDefinition("entity:{$map[$base_table]}");
        if (!$resolved = $this->typeResolver->resolveRecursive($definition)) {
          continue;
        }

        $displays[$display_id] = [
          'type' => new ListModifier($resolved),
          'resolve' => [__CLASS__, 'resolveDisplay'],
          'args' => $filters,
        ];
      }

      if (!empty($displays)) {
        // Format the display names as camel-cased strings.
        $names = String::formatPropertyNameList(array_keys($displays));
        $displays = array_combine($names, $displays);

        $id = $view->id();
        $name = String::formatPropertyName($id);
        $fields[$name] = [
          'type' => new ObjectType($name, $displays),
          'resolve' => [__CLASS__, 'resolveView'],
          'resolveData' => ['id' => $id],
        ];
      }
    }

    return !empty($fields) ? ['views' => [
      'type' => new ObjectType('__ViewsRoot', $fields),
      'resolve' => [__CLASS__, 'resolveRoot']
    ]] : [];
  }

  public static function resolveRoot() {
    return TRUE;
  }

  /**
   * @param $source
   * @param array|null $args
   * @param $root
   * @param Node $field
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   */
  public static function resolveView($source, array $args = NULL, $root, Node $field) {
    // @todo Fix injection of container dependencies in resolver functions.
    $storage = \Drupal::entityManager()->getStorage('view');
    if ($view = $storage->load($field->get('name')->get('value'))) {
      return $view;
    }

    return NULL;
  }

  /**
   * @param $source
   * @param array|null $args
   * @param $root
   * @param Node $field
   *
   * @return array
   */
  public static function resolveDisplay($source, array $args = NULL, $root, Node $field) {
    if ($source instanceof ViewEntityInterface) {
      $executable = $source->getExecutable();
      $executable->setDisplay($field->get('name')->get('value'));
      $executable->setExposedInput(array_filter($args));
      $executable->execute();

      $result = [];
      foreach ($executable->result as $row) {
        $result[$row->_entity->id()] = $row->_entity->getTypedData();
      }

      return $result;
    }
  }
}
