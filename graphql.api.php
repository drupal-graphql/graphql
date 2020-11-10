<?php

/**
 * @file
 * Hooks provided by GraphQL module.
 */

/**
 * Alter the query built by the term autocomplete data producer.
 *
 * @param array $args
 *   Input arguments of taxonomy term data producer.
 * @param \Drupal\Core\Database\Query\SelectInterface $query
 *   The term autocomplete query.
 * @param \Drupal\Core\Database\Query\ConditionInterface $name_condition_group
 *   The condition group matching the term name. This condition group is defined
 *   as OR condition group which allows to cover a match in term name OR in some
 *   other fields.
 */
function hook_graphql_term_autocomplete_query_alter(array $args, \Drupal\Core\Database\Query\SelectInterface $query, \Drupal\Core\Database\Query\ConditionInterface $name_condition_group): void {
  // Custom field on profile entity type of bundle resume has a reference to
  // synonyms field. Extend a query so it matches the string in term names OR in
  // synonyms.
  if ($args['entity_type'] == 'profile' && $args['entity_type'] == 'resume' && $args['field'] = 'field_custom') {
    $like_contains = '%' . $query->escapeLike($args['match_string']) . '%';
    $query->leftJoin('taxonomy_term__field_term_synonyms', 's', 's.entity_id = t.tid');
    // This makes the query to perform a match on term names OR synonyms.
    $name_condition_group->condition('s.field_term_synonyms_synonym', $like_contains, 'LIKE');
  }
}
