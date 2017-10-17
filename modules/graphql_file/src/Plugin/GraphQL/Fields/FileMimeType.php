<?php

namespace Drupal\graphql_file\Plugin\GraphQL\Fields;

use Drupal\file\FileInterface;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve a files mime type.
 *
 * @GraphQLField(
 *   id = "file_mime_type",
 *   secure = true,
 *   name = "mimeType",
 *   type = "String",
 *   parents = {"File"}
 * )
 */
class FileMimeType extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof FileInterface) {
      yield $value->getMimeType();
    }
  }

}
