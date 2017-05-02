<?php

namespace Drupal\graphql_file\Plugin\GraphQL\Fields;

use Drupal\Core\Url;
use Drupal\file\FileInterface;
use Drupal\graphql_content\Plugin\GraphQL\Fields\EntityUrl;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Entity route field override for files.
 *
 * @GraphQLField(
 *   name = "entityUrl",
 *   type = "Url",
 *   types = {"File"}
 * )
 */
class FileUrl extends EntityUrl {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof FileInterface) {
      yield Url::fromUri($value->url());
    }
  }

}