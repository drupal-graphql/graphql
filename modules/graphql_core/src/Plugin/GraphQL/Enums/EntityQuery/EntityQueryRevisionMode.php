<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Enums\EntityQuery;

use Drupal\graphql\Plugin\GraphQL\Enums\EnumPluginBase;

/**
 * @GraphQLEnum(
 *   id = "entity_query_revision_mode",
 *   name = "EntityQueryRevisionMode",
 *   values = {
 *     "DEFAULT" = {
 *       "value" = "default",
 *       "description" = @Translation("Loads the current (default) revisions."),
 *     },
 *     "ALL" = {
 *       "value" = "all",
 *       "description" = @Translation("Loads all revisions."),
 *     },
 *     "LATEST" = {
 *       "value" = "latest",
 *       "description" = @Translation("Loads latest revision."),
 *     }
 *   }
 * )
 */
class EntityQueryRevisionMode extends EnumPluginBase {

}
