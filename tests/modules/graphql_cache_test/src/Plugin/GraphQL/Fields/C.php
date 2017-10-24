<?php

namespace Drupal\graphql_cache_test\Plugin\GraphQL\Fields;

/**
 * An uncacheable counter field.
 *
 * @GraphQLField(
 *   id = "c",
 *   secure = true,
 *   name = "c",
 *   type = "Int",
 *   parents = {"Root"},
 *   response_cache_tags = {"c", "graphql_response"},
 *   response_cache_contexts = {"graphql_test_root_field", "user"}
 * )
 */
class C extends A {

}
