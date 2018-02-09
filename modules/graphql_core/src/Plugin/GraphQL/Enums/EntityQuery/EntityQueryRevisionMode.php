<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Enums\EntityQuery;

use Drupal\graphql\Plugin\GraphQL\Enums\EnumPluginBase;

/**
 * @GraphQLEnum(
 *   id = "entity_query_revision_mode",
 *   name = "EntityQueryRevisionMode",
 *   values = {
 *     "default" = {
 *       "name" = "default",
 *       "description" = @Translation("Loads the current (default) revisions."),
 *     },
 *     "all" = {
 *       "name" = "all",
 *       "description" = @Translation("Loads all revisions."),
 *     }
 *   }
 * )
 */
class EntityQueryRevisionMode extends EnumPluginBase {

}
