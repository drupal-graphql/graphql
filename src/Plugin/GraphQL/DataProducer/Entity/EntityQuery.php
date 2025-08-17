<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\GraphQL\Execution\FieldContext;

/**
 * Builds and executes Drupal entity query.
 *
 * Example for mapping this dataproducer to the schema:
 * @code
 *   $defaultSorting = [
 *     [
 *       'field' => 'created',
 *       'direction' => 'DESC',
 *     ],
 *   ];
 *   $registry->addFieldResolver('Query', 'jobApplicationsByUserId',
 *     $builder->compose(
 *       $builder->fromArgument('id'),
 *       $builder->callback(function ($uid) {
 *         $conditions = [
 *           [
 *             'field' => 'uid',
 *             'value' => [$uid],
 *           ],
 *         ];
 *         return $conditions;
 *       }),
 *       $builder->produce('entity_query', [
 *         'type' => $builder->fromValue('node'),
 *         'conditions' => $builder->fromParent(),
 *         'offset' => $builder->fromArgument('offset'),
 *         'limit' => $builder->fromArgument('limit'),
 *         'language' => $builder->fromArgument('language'),
 *         'allowed_filters' => $builder->fromValue(['uid']),
 *         'bundles' => $builder->fromValue(['job_application']),
 *         'sorts' => $builder->fromArgumentWithDefaultValue('sorting', $defaultSorting),
 *       ]),
 *       $builder->produce('entity_load_multiple', [
 *         'type' => $builder->fromValue('node'),
 *         'ids' => $builder->fromParent(),
 *       ]),
 *     )
 *   );
 * @endcode
 */
#[DataProducer(
  id: "entity_query",
  name: new TranslatableMarkup("Load entities"),
  description: new TranslatableMarkup("Returns entity IDs for a given query"),
  produces: new ContextDefinition(
    data_type: "string",
    label: new TranslatableMarkup("Entity IDs"),
    multiple: TRUE,
  ),
  consumes: [
    "type" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("Entity type"),
    ),
    "limit" => new ContextDefinition(
      data_type: "integer",
      label: new TranslatableMarkup("Limit"),
      required: FALSE,
      default_value: 10,
    ),
    "offset" => new ContextDefinition(
      data_type: "integer",
      label: new TranslatableMarkup("Offset"),
      required: FALSE,
      default_value: 0,
    ),
    "owned_only" => new ContextDefinition(
      data_type: "boolean",
      label: new TranslatableMarkup("Query only owned entities"),
      required: FALSE,
      default_value: FALSE,
    ),
    "conditions" => new ContextDefinition(
      data_type: "any",
      label: new TranslatableMarkup("Conditions"),
      multiple: TRUE,
      required: FALSE,
      default_value: [],
    ),
    "allowed_filters" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("Allowed filters"),
      multiple: TRUE,
      required: FALSE,
      default_value: [],
    ),
    "languages" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("Entity languages"),
      multiple: TRUE,
      required: FALSE,
      default_value: [],
    ),
    "bundles" => new ContextDefinition(
      data_type: "any",
      label: new TranslatableMarkup("Entity bundles"),
      multiple: TRUE,
      required: FALSE,
      default_value: [],
    ),
    "access" => new ContextDefinition(
      data_type: "boolean",
      label: new TranslatableMarkup("Check access"),
      required: FALSE,
      default_value: TRUE,
    ),
    "sorts" => new ContextDefinition(
      data_type: "any",
      label: new TranslatableMarkup("Sorts"),
      multiple: TRUE,
      default_value: [],
      required: FALSE,
    ),
  ],
)]
class EntityQuery extends EntityQueryBase {

  /**
   * The default maximum number of items to be capped to prevent DDOS attacks.
   */
  const MAX_ITEMS = 100;

  /**
   * Resolves the entity query.
   *
   * @param string $type
   *   Entity type.
   * @param int $limit
   *   Maximum number of queried entities.
   * @param int $offset
   *   Offset to start with.
   * @param bool $ownedOnly
   *   Query only entities owned by current user.
   * @param array $conditions
   *   List of conditions to filter the entities.
   * @param array $allowedFilters
   *   List of fields to be used in conditions to restrict access to data.
   * @param array<string> $languages
   *   Languages for queried entities.
   * @param array<string> $bundles
   *   List of bundles to be filtered.
   * @param bool $access
   *   Whether entity query should check access.
   * @param array $sorts
   *   List of sorts.
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $context
   *   The caching context related to the current field.
   *
   * @return array
   *   The list of ids that match this query.
   *
   * @throws \GraphQL\Error\UserError
   *   No bundles defined for given entity type.
   */
  public function resolve(string $type, int $limit, int $offset, bool $ownedOnly, array $conditions, array $allowedFilters, array $languages, array $bundles, bool $access, array $sorts, FieldContext $context): array {
    $query = $this->buildBaseEntityQuery(
      $type,
      $ownedOnly,
      $conditions,
      $allowedFilters,
      $languages,
      $bundles,
      $access,
      $context
    );

    // Make sure offset is zero or positive.
    $offset = max($offset, 0);

    // Make sure limit is positive and cap the max items to prevent DDOS
    // attacks.
    if ($limit <= 0) {
      $limit = 10;
    }
    $limit = min($limit, self::MAX_ITEMS);

    // Apply offset and limit.
    $query->range($offset, $limit);

    // Add sorts.
    foreach ($sorts as $sort) {
      if (!empty($sort['field'])) {
        if (!empty($sort['direction']) && strtolower($sort['direction']) == 'desc') {
          $direction = 'DESC';
        }
        else {
          $direction = 'ASC';
        }
        $query->sort($sort['field'], $direction);
      }
    }

    $ids = $query->execute();

    return $ids;
  }

}
