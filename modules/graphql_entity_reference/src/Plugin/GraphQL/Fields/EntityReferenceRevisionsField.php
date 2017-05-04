<?php

namespace Drupal\graphql_entity_reference\Plugin\GraphQL\Fields;

/**
 * Expose entity reference revisions fields as objects.
 *
 * @GraphQLField(
 *   id = "entity_reference_revisions",
 *   field_formatter = "entity_reference_revisions_entity_view",
 *   cache_tags = {"entity_field_info"},
 *   deriver = "Drupal\graphql_entity_reference\Plugin\Deriver\EntityReferenceRevisionsFields"
 * )
 */
class EntityReferenceRevisionsField extends EntityReferenceField {

}
