<?php

namespace Drupal\graphql_json\Plugin\GraphQL\Fields;

use Drupal\file\FileInterface;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Expose json file contents.
 *
 * @GraphQLField(
 *   id = "json_file",
 *   name = "json",
 *   secure = true,
 *   type = "JsonNode",
 *   types = {"File"},
 * )
 */
class JsonFile extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof FileInterface) {
      if ($content = file_get_contents($value->getFileUri())) {
        yield json_decode($content, TRUE);
      }
    }
  }

}
