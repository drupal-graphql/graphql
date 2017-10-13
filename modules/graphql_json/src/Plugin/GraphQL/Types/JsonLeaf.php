<?php
namespace Drupal\graphql_json\Plugin\GraphQL\Types;


use Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase;

/**
 * GraphQL type for json list nodes.
 *
 * @GraphQLType(
 *   id = "json_leaf",
 *   name = "JsonLeaf",
 *   unions = {"JsonNode"}
 * )
 */
class JsonLeaf extends TypePluginBase {

  /**
   * {@inheritdoc}
   */
  public function isValidValue($value) {
    return !(is_object($value) || is_array($value));
  }

  /**
   * {@inheritdoc}
   */
  public function applies($value) {
    return !is_array($value);
  }


}