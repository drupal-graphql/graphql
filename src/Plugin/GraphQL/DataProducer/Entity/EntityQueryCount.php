<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\graphql\GraphQL\Execution\FieldContext;

/**
 * Builds and executes Drupal entity query count.
 *
 * @DataProducer(
 *   id = "entity_query_count",
 *   name = @Translation("Load entities"),
 *   description = @Translation("Loads entities."),
 *   produces = @ContextDefinition("integer",
 *     label = @Translation("Total count of items queried by entity query."),
 *   ),
 *   consumes = {
 *     "type" = @ContextDefinition("string",
 *       label = @Translation("Entity type")
 *     ),
 *     "owned_only" = @ContextDefinition("boolean",
 *       label = @Translation("Query only owned entities"),
 *       required = FALSE,
 *       default_value = FALSE
 *     ),
 *     "conditions" = @ContextDefinition("any",
 *       label = @Translation("Conditions"),
 *       multiple = TRUE,
 *       required = FALSE,
 *       default_value = {}
 *     ),
 *     "allowed_filters" = @ContextDefinition("string",
 *       label = @Translation("Allowed filters"),
 *       multiple = TRUE,
 *       required = FALSE,
 *       default_value = {}
 *     ),
 *     "languages" = @ContextDefinition("string",
 *       label = @Translation("Entity languages(s)"),
 *       multiple = TRUE,
 *       required = FALSE,
 *       default_value = {}
 *     ),
 *     "bundles" = @ContextDefinition("any",
 *       label = @Translation("Entity bundle(s)"),
 *       multiple = TRUE,
 *       required = FALSE,
 *       default_value = {}
 *     )
 *   }
 * )
 */
class EntityQueryCount extends EntityQueryBase {

  /**
   * Resolves the entity query count.
   *
   * @param string $type
   *   Entity type.
   * @param int $limit
   *   Maximum number of queried entities.
   * @param int|null $offset
   *   Offset to start with.
   * @param bool $ownedOnly
   *   Query only entities owned by current user.
   * @param array $conditions
   *   List of conditions to filter the entities.
   * @param array $allowedFilters
   *   List of fields to be used in conditions to restrict access to data.
   * @param string[] $languages
   *   Languages for queried entities.
   * @param string[] $bundles
   *   List of bundles to be filtered.
   * @param array $sorts
   *   List of sorts.
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $context
   *   The caching context related to the current field.
   *
   * @return int
   *   Total count of items queried by entity query.
   */
  public function resolve(string $type, int $limit, int $offset, bool $ownedOnly, array $conditions, array $allowedFilters, array $languages, array $bundles, array $sorts, FieldContext $context): int {
    $query = $this->buildBaseEntityQuery(
      $type,
      $ownedOnly,
      $conditions,
      $allowedFilters,
      $languages,
      $bundles,
      $context
    );

    return $query->count()->execute();
  }

}
