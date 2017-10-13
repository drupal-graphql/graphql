<?php

namespace Drupal\graphql_cache_test\Plugin\GraphQL\Fields;

/**
 * An uncacheable counter field.
 *
 * @GraphQLField(
 *   id = "b",
 *   secure = true,
 *   name = "b",
 *   type = "Int",
 *   types = {"Root", "Object"},
 *   response_cache_tags = {"b"},
 *   response_cache_contexts = {"graphql_test"}
 * )
 */
class B extends A {

}
