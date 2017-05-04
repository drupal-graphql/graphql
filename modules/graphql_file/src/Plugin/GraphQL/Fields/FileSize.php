<?php

namespace Drupal\graphql_file\Plugin\GraphQL\Fields;

use Drupal\file\FileInterface;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve a files mime type.
 *
 * @GraphQLField(
 *   id = "file_size",
 *   name = "fileSize",
 *   type = "Int",
 *   types = {"File"}
 * )
 */
class FileSize extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof FileInterface) {
      yield intval($value->getSize());
    }
  }

}
