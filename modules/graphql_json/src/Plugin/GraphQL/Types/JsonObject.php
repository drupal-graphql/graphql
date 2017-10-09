<?php
namespace Drupal\graphql_json\Plugin\GraphQL\Types;


use Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase;

/**
 * GraphQL type for json object nodes.
 *
 * @GraphQLType(
 *   id = "json_object",
 *   name = "JsonObject",
 *   unions = {"JsonNode"}
 * )
 */
class JsonObject extends TypePluginBase {

  /**
   * {@inheritdoc}
   */
  public function applies($value) {
    return is_array($value) && count(array_filter(array_keys($value), 'is_string')) > 0;
  }
}