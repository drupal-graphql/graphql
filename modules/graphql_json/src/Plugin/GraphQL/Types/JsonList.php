<?php
namespace Drupal\graphql_json\Plugin\GraphQL\Types;


use Drupal\graphql_core\GraphQL\TypePluginBase;

/**
 * GraphQL type for json list nodes.
 *
 * @GraphQLType(
 *   id = "json_list",
 *   name = "JsonList",
 *   interfaces = {"JsonNode"}
 * )
 */
class JsonList extends TypePluginBase {

  /**
   * {@inheritdoc}
   */
  public function applies($value) {
    return is_array($value) && count(array_filter(array_keys($value), 'is_string')) == 0;
  }

}
