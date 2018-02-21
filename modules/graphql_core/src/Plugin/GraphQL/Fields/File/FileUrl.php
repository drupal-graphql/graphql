<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\File;

use Drupal\file\FileInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Entity route field override for files.
 *
 * @GraphQLField(
 *   id = "file_url",
 *   secure = true,
 *   name = "url",
 *   type = "String",
 *   parents = {"File"}
 * )
 */
class FileUrl extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof FileInterface) {
      yield file_create_url($value->getFileUri());
    }
  }

}
