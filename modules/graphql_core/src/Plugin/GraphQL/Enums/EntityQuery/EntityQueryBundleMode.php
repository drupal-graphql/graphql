<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Enums\EntityQuery;

use Drupal\graphql\Plugin\GraphQL\Enums\EnumPluginBase;

/**
 * @GraphQLEnum(
 *   id = "entity_query_bundle_mode",
 *   name = "EntityQueryBundleMode",
 *   values = {
 *     "same" = {
 *       "name" = "same",
 *       "description" = @Translation("Loads only entities that share the same bundle with the parent entity."),
 *     },
 *     "all" = {
 *       "name" = "all",
 *       "description" = @Translation("Loads entities across all bundles."),
 *     }
 *   }
 * )
 */
class EntityQueryBundleMode extends EnumPluginBase {

}
