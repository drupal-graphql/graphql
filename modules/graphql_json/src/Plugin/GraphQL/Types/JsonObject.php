<?php
namespace Drupal\graphql_json\Plugin\GraphQL\Types;


use Drupal\graphql_core\GraphQL\TypePluginBase;

/**
 * GraphQL type for json object nodes.
 *
 * @GraphQLType(
 *   id = "json_object",
 *   name = "JsonObject",
 *   interfaces = {"JsonNode"}
 * )
 */
class JsonObject extends TypePluginBase {

}