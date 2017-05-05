<?php

namespace Drupal\graphql_cache_test\Plugin\GraphQL\Fields;

/**
 * An uncacheable counter field.
 *
 * @GraphQLField(
 *   id = "b",
 *   name = "b",
 *   type = "Int",
 *   types = {"Root", "Object"},
 *   cache_tags = {"b"},
 *   cache_contexts = {"graphql_test"}
 * )
 */
class B extends A {

}
