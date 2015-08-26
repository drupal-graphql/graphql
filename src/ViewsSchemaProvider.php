<?php

/**
 * @file
 * Contains \Drupal\graphql\ViewsSchemaProvider.
 */

namespace Drupal\graphql;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Fubhy\GraphQL\Language\Node;
use Fubhy\GraphQL\Type\Definition\Types\ListModifier;
use Fubhy\GraphQL\Type\Definition\Types\NonNullModifier;
use Fubhy\GraphQL\Type\Definition\Types\Type;

/**
 * Generates a GraphQL Schema for content entity types.
 */
class ViewsSchemaProvider extends SchemaProviderBase implements SchemaProviderInterface {
  protected $entityManager;
  /**
   * @var EntitySchemaProviderInterface
   */
  protected $entitySchemaProvider;

  /**
   * Constructs a EntitySchemaProvider object.
   *
   * @param EntityManagerInterface $entityManager
   *   The entity manager service.
   * @param EntitySchemaProviderInterface $entitySchemaProvider
   */
  public function __construct(EntityManagerInterface $entityManager, EntitySchemaProviderInterface $entitySchemaProvider) {
    $this->entityManager = $entityManager;
    $this->entitySchemaProvider = $entitySchemaProvider;
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
    $schema = [];

    $map = $this->getDataTableMap();
    foreach ($this->entityManager->getStorage('view')->loadMultiple() as $view) {
      $base_table = $view->get('base_table');
      if (!isset($map[$base_table])) {
        continue;
      }

      $view_id = $view->id();

      /** @var \Drupal\views\ViewEntityInterface $view */
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

        $schema_key = $this->underscoreToCamelCase("view_{$view_id}_{$display_id}");
        $schema[$schema_key] = [
          'type' => new ListModifier($this->entitySchemaProvider->getEntityTypeInterface($map[$base_table])),
          'resolve' => $this->getViewResolver($view_id, $display_id),
          'args' => array_merge($filters),
        ];
      }
    }

    return $schema;
  }

  /**
   * @param string $view_id
   *
   * @return callable
   */
  protected function getViewResolver($view_id, $display_id) {
    return function ($source, array $args = []) use ($view_id, $display_id) {
      if ($view = $this->entityManager->getStorage('view')->load($view_id)) {
        /** @var \Drupal\views\ViewEntityInterface $view */
        $executable = $view->getExecutable();
        $executable->setDisplay($display_id);
        $executable->setExposedInput(array_filter($args));
        $executable->execute();

        $result = [];
        foreach ($executable->result as $row) {
          $result[$row->_entity->id()] = $row->_entity;
        }

        return $result;
      }

      return [];
    };
  }

  /**
   * @param string $string
   *
   * @return string
   */
  protected function underscoreToCamelCase($string) {
    $words = explode('_', strtolower($string));
    return lcfirst(implode('', array_map('ucfirst', array_map('trim', $words))));
  }
}
