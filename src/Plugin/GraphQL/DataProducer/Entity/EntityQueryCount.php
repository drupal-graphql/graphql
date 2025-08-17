<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\GraphQL\Execution\FieldContext;

/**
 * Builds and executes Drupal entity query count.
 *
 * It supposed to be used along the entity query to get the total amount of
 * items. This is important for pagination when the total amount needs to be
 * known to derive the number of available pages. Otherwise the query works the
 * same way as entity query. Same filters are applied, just skips the offset and
 * limit, and turns the query into count query.
 *
 * Example for mapping this dataproducer to the schema:
 * @code
 *   $defaultSorting = [
 *     [
 *       'field' => 'created',
 *       'direction' => 'DESC',
 *     ],
 *   ];
 *   $registry->addFieldResolver('Query', 'jobApplicationsByUserIdCount',
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
 *       $builder->produce('entity_query_count', [
 *         'type' => $builder->fromValue('node'),
 *         'conditions' => $builder->fromParent(),
 *         'offset' => $builder->fromArgument('offset'),
 *         'limit' => $builder->fromArgument('limit'),
 *         'language' => $builder->fromArgument('language'),
 *         'allowed_filters' => $builder->fromValue(['uid']),
 *         'bundles' => $builder->fromValue(['job_application']),
 *         'sorts' => $builder->fromArgumentWithDefaultValue('sorting', $defaultSorting),
 *       ])
 *     )
 *   );
 * @endcode
 */
#[DataProducer(
  id: "entity_query_count",
  name: new TranslatableMarkup("Load entities"),
  description: new TranslatableMarkup("Loads entities."),
  produces: new ContextDefinition(
    data_type: "integer",
    label: new TranslatableMarkup("Total count of items queried by entity query."),
  ),
  consumes: [
    "type" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("Entity type"),
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
  ],
)]
class EntityQueryCount extends EntityQueryBase {

  /**
   * Resolves the entity query count.
   *
   * @param string $type
   *   Entity type.
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
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $context
   *   The caching context related to the current field.
   *
   * @return int
   *   Total count of items queried by entity query.
   */
  public function resolve(string $type, bool $ownedOnly, array $conditions, array $allowedFilters, array $languages, array $bundles, bool $access, FieldContext $context): int {
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

    return $query->count()->execute();
  }

}
