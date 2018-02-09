<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\EntityQuery;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\Query\QueryInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Exception\ResolveException;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * @GraphQLField(
 *   id = "entity_query",
 *   secure = true,
 *   type = "EntityQueryResult!",
 *   arguments = {
 *     "filter" = "EntityQueryFilterInput",
 *     "sort" = "[EntityQuerySortInput]",
 *     "offset" = {
 *       "type" = "Int",
 *       "default" = 0
 *     },
 *     "limit" = {
 *       "type" = "Int",
 *       "default" = 10
 *     },
 *     "revisions" = {
 *       "type" = "EntityQueryRevisionMode",
 *       "default" = "default"
 *     }
 *   },
 *   deriver = "Drupal\graphql_core\Plugin\Deriver\Fields\EntityQueryDeriver"
 * )
 */
class EntityQuery extends FieldPluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function getCacheDependencies(array $result, $value, array $args, ResolveInfo $info) {
    $entityTypeId = $this->pluginDefinition['entity_type'];
    $type = $this->entityTypeManager->getDefinition($entityTypeId);

    $metadata = new CacheableMetadata();
    $metadata->addCacheTags($type->getListCacheTags());
    $metadata->addCacheContexts($type->getListCacheContexts());

    return [$metadata];
  }

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    yield $this->getQuery($value, $args, $info);
  }

  /**
   * Create an entity query for the plugin's entity type.
   *
   * @param mixed $value
   *   The parent entity type.
   * @param array $args
   *   The field arguments array.
   * @param \Youshido\GraphQL\Execution\ResolveInfo $info
   *   The resolve info object.
   *
   * @return \Drupal\Core\Entity\Query\QueryInterface
   *   The entity query object.
   */
  protected function getQuery($value, array $args, ResolveInfo $info) {
    $entityTypeId = $this->pluginDefinition['entity_type'];
    $entityStorage = $this->entityTypeManager->getStorage($entityTypeId);

    $query = $entityStorage->getQuery();
    $query->range($args['offset'], $args['limit']);
    $query->accessCheck(TRUE);

    // Check if this is a query for all entity revisions.
    if (!empty($args['revisions']) && $args['revisions'] === 'all') {
      // Mark the query as such and sort by the revision id too.
      $query->allRevisions();
      $query->addTag('revisions');
    }

    if (!empty($args['filter'])) {
      $this->applyFilter($query, $args['filter']);
    }

    if (!empty($args['sort'])) {
      $this->applySort($query, $args['sort']);
    }

    return $query;
  }

  /**
   * Apply the specified sort directives to the query.
   *
   * @param \Drupal\Core\Entity\Query\QueryInterface $query
   *   The entity query object.
   * @param array $sort
   *   The sort definitions from the field arguments.
   */
  protected function applySort(QueryInterface $query, array $sort) {
    foreach ($sort as $item) {
      $direction = !empty($item['direction']) ? $item['direction'] : 'DESC';
      $query->sort($item['field'], $direction);
    }
  }

  /**
   * Apply the specified filter conditions to the query.
   *
   * Recursively picks up all filters and aggregates them into condition groups
   * according to the nested structure of the filter argument.
   *
   * @param \Drupal\Core\Entity\Query\QueryInterface $query
   *   The entity query object.
   * @param array $filter
   *   The filter definitions from the field arguments.
   */
  protected function applyFilter(QueryInterface $query, array $filter) {
    $query->condition($this->buildFilterConditions($query, $filter));
  }

  /**
   * Recursively builds the filter condition groups.
   *
   * @param \Drupal\Core\Entity\Query\QueryInterface $query
   *   The entity query object.
   * @param array $filter
   *   The filter definitions from the field arguments.
   *
   * @return \Drupal\Core\Entity\Query\ConditionInterface
   *   The generated condition group according to the given filter definitions.
   *
   * @throws \Youshido\GraphQL\Exception\ResolveException
   *   If the given operator and value for a filter are invalid.
   */
  protected function buildFilterConditions(QueryInterface $query, array $filter) {
    $conjunction = !empty($filter['conjunction']) ? $filter['conjunction'] : 'AND';
    $group = $conjunction === 'AND' ? $query->andConditionGroup() : $query->orConditionGroup();

    // Apply filter conditions.
    $conditions = !empty($filter['conditions']) ? $filter['conditions'] : [];
    foreach ($conditions as $condition) {
      $field = $condition['field'];
      $value = !empty($condition['value']) ? $condition['value'] : NULL;
      $operator = !empty($condition['operator']) ? $condition['operator'] : NULL;
      $language = !empty($condition['language']) ? $condition['language'] : NULL;

      // We need at least a value or an operator.
      if (empty($operator) && empty($value)) {
        throw new ResolveException(sprintf("Missing value and operator in filter for '%s'.", $field));
      }
      // Unary operators need a single value.
      else if (!empty($operator) && $this->isUnaryOperator($operator)) {
        if (empty($value) || count($value) > 1) {
          throw new ResolveException(sprintf("Unary operators must be associated with a single value (field '%s').", $field));
        }

        // Pick the first item from the values.
        $value = reset($value);
      }
      // Range operators need exactly two values.
      else if (!empty($operator) && $this->isRangeOperator($operator)) {
        if (empty($value) || count($value) !== 2) {
          throw new ResolveException(sprintf("Range operators must require exactly two values (field '%s').", $field));
        }
      }
      // Null operators can't have a value set.
      else if (!empty($operator) && $this->isNullOperator($operator)) {
        if (!empty($value)) {
          throw new ResolveException(sprintf("Null operators must not be associated with a filter value (field '%s').", $field));
        }
      }

      // If no operator is set, however, we default to EQUALS or IN, depending
      // on whether the given value is an array with one or more than one items.
      if (empty($operator)) {
        $value = count($value) === 1 ? reset($value) : $value;
        $operator = is_array($value) ? 'IN' : '=';
      }

      // Add the condition for the current field.
      $group->condition($field, $value, $operator, $language);
    }

    // Apply nested filter group conditions.
    $groups = !empty($filter['groups']) ? $filter['groups'] : [];
    foreach ($groups as $args) {
      // By default, we use AND condition groups.
      $group->condition($this->buildFilterConditions($query, $args));
    }

    return $group;
  }

  /**
   * Checks if an operator is a unary operator.
   *
   * @param string $operator
   *   The query operator to check against.
   *
   * @return bool
   *   TRUE if the given operator is unary, FALSE otherwise.
   */
  protected function isUnaryOperator($operator) {
    $unary = ["=", "<>", "<", "<=", ">", ">=", "LIKE", "NOT LIKE"];
    return in_array($operator, $unary);
  }

  /**
   * Checks if an operator is a null operator.
   *
   * @param string $operator
   *   The query operator to check against.
   *
   * @return bool
   *   TRUE if the given operator is a null operator, FALSE otherwise.
   */
  protected function isNullOperator($operator) {
    $null = ["IS NULL", "IS NOT NULL"];
    return in_array($operator, $null);
  }

  /**
   * Checks if an operator is a range operator.
   *
   * @param string $operator
   *   The query operator to check against.
   *
   * @return bool
   *   TRUE if the given operator is a range operator, FALSE otherwise.
   */
  protected function isRangeOperator($operator) {
    $null = ["BETWEEN", "NOT BETWEEN"];
    return in_array($operator, $null);
  }

}
