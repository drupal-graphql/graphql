<?php

namespace Drupal\graphql_cache_test\Plugin\GraphQL\Fields;

use Drupal\graphql_core\GraphQL\Traits\CacheablePluginTrait;

/**
 * An uncacheable counter field.
 *
 * @GraphQLField(
 *   id = "cacheable_field",
 *   name = "cacheable",
 *   type = "Int",
 *   types = {"Root", "Object"},
 *   cache_tags = {"graphql_test"},
 *   cache_contexts = {"graphql_test"},
 *   arguments={
 *     "amount" = {
 *       "type" = "Int",
 *       "default" = 1,
 *       "nullable" = true
 *     }
 *   }
 * )
 */
class CacheableField extends UncacheableField {
  use CacheablePluginTrait;
}
