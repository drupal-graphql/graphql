<?php

/**
 * @file
 * Contains \Drupal\graphql\SchemaProvider\ViewsSchemaProvider.
 */

namespace Drupal\graphql\SchemaProvider;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\TypedData\TypedDataManager;
use Drupal\graphql\TypeResolverInterface;
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
   * Constructs a ViewsSchemaProvider object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entityManager
   *   The entity manager service.
   * @param TypedDataManager $typedDataManager
   *   The typed data manager service.
   * @param \Drupal\graphql\TypeResolverInterface $typeResolver
   *   The base type resolver service.
   */
  public function __construct(EntityManagerInterface $entityManager, TypedDataManager $typedDataManager, TypeResolverInterface $typeResolver) {
    $this->entityManager = $entityManager;
    $this->typeResolver = $typeResolver;
    $this->typedDataManager = $typedDataManager;
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
        $displays[$display_id] = [
          'type' => new ListModifier($this->typeResolver->resolveRecursive($definition)),
          'resolve' => [__CLASS__, 'resolveDisplay'],
          'args' => array_merge($filters),
        ];
      }

      if (!empty($displays)) {
        $fields[$view->id()] = [
          'type' => new ObjectType($this->stringToName($view->id()), $displays),
          'resolve' => [__CLASS__, 'resolveView'],
        ];
      }
    }

    return !empty($fields) ? ['views' => [
      'type' => new ObjectType('__ViewsRoot', $fields),
      'resolve' => function () {
        return $this->entityManager->getStorage('view');
      }
    ]] : [];
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
    if ($source instanceof EntityStorageInterface) {
      return $source->load($field->get('name')->get('value'));
    }
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

  /**
   * @param $string
   *
   * @return string
   */
  protected function stringToName($string) {
    $words = preg_split('/[:\.\-_]/', strtolower($string));
    return implode('', array_map('ucfirst', array_map('trim', $words)));
  }
}
