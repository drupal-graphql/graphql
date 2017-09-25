<?php

namespace Drupal\graphql_json\Plugin\GraphQL\Interfaces;

use Drupal\graphql_core\GraphQL\InterfacePluginBase;

/**
 * GraphQL interface for JSON objects.
 *
 * @GraphQLInterface(
 *   id = "json_node",
 *   name = "JsonNode"
 * )
 */
class JsonNode extends InterfacePluginBase {

  /**
   * {@inheritdoc}
   */
  public function isValidValue($value) {
    return !is_object($value);
  }


}