<?php

namespace Drupal\graphql_cache_test\Plugin\GraphQL\Fields;

/**
 * An uncacheable counter field.
 *
 * @GraphQLField(
 *   id = "c",
 *   name = "c",
 *   type = "Int",
 *   types = {"Root"},
 *   cache_tags = {"c"},
 *   cache_contexts = {"graphql_test_root_field"}
 * )
 */
class C extends A {

}
