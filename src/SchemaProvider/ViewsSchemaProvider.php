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
use Drupal\graphql\Utility\StringHelper;
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
   * @param \Drupal\Core\Entity\EntityManagerInterface $entityManager
   *   The entity manager service.
   * @param TypedDataManager $typedDataManager
   *   The typed data manager service.
   * @param \Drupal\graphql\TypeResolverInterface $typeResolver
   *   The base type resolver service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler service.
   */
  public function __construct(EntityManagerInterface $entityManager, TypedDataManager $typedDataManager, TypeResolverInterface $typeResolver, ModuleHandlerInterface $moduleHandler) {
    $this->entityManager = $entityManager;
    $this->typeResolver = $typeResolver;
    $this->typedDataManager = $typedDataManager;
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * @return array
   */
  protected function getDataTableMap() {
    $map = [];
    foreach ($this->entityManager->getDefinitions() as $entityTypeId => $entityType) {
      if ($dataTable = $entityType->getDataTable()) {
        $map[$dataTable] = $entityType->id();
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
      $baseTable = $view->get('base_table');
      if (!isset($map[$baseTable])) {
        continue;
      }

      $viewId = $view->id();
      foreach ($view->get('display') as $displayId => $display) {
        if ($display['display_plugin'] !== 'graphql') {
          continue;
        }

        $executable = $view->getExecutable();
        $executable->setDisplay($displayId);

        $filters = $executable->getDisplay()->getOption('filters');
        $filters = array_reduce(array_filter($filters, function (array $filter) {
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

        $definition = $this->typedDataManager->createDataDefinition("entity:{$map[$baseTable]}");
        if (!$resolved = $this->typeResolver->resolveRecursive($definition)) {
          continue;
        }

        $fields["$viewId:$displayId"] = [
          'type' => new ListModifier($resolved),
          'resolve' => [__CLASS__, 'getViewResults'],
          'args' => $filters,
          'resolveData' => ['view' => $viewId, 'display' => $displayId],
        ];
      }
    }

    $names = StringHelper::formatPropertyNameList(array_keys($fields));
    return array_combine($names, $fields);
  }

  /**
   * Views result resolver callback.
   */
  public static function getViewResults($source, array $args = NULL, $root, Node $field, $a, $b, $c, $data) {
    // @todo Fix injection of container dependencies in resolver functions.
    $storage = \Drupal::entityManager()->getStorage('view');
    /** @var \Drupal\views\ViewEntityInterface $view */
    if (!$view = $storage->load($data['view'])) {
      return NULL;
    }

    $executable = $view->getExecutable();
    $executable->setDisplay($data['display']);
    $executable->setExposedInput(array_filter($args));
    $executable->execute();

    $result = [];
    foreach ($executable->result as $row) {
      $entity = $row->_entity;
      $result[$entity->id()] = $entity->getTypedData();
    }

    return $result;
  }
}
