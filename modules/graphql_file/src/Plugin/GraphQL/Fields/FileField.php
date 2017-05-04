<?php

namespace Drupal\graphql_file\Plugin\GraphQL\Fields;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Replace Drupal file fields with file entities.
 *
 * @GraphQLField(
 *   id = "file",
 *   field_formatter = "file_url_plain",
 *   type = "File",
 *   cache_tags = {"entity_field_info"},
 *   deriver = "Drupal\graphql_content\Plugin\Deriver\FieldFormatterDeriver"
 * )
 */
class FileField extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof ContentEntityInterface) {
      foreach ($value->get($this->getPluginDefinition()['field']) as $item) {
        yield $item->entity;
      }
    }
  }
}
