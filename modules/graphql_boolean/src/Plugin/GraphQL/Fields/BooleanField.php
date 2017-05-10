<?php

namespace Drupal\graphql_boolean\Plugin\GraphQL\Fields;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Expose boolean fields as boolean values.
 *
 * @GraphQLField(
 *   id = "boolean_field",
 *   field_formatter = "boolean",
 *   type = "Boolean",
 *   deriver = "Drupal\graphql_content\Plugin\Deriver\FieldFormatterDeriver"
 * )
 */
class BooleanField extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof ContentEntityInterface) {
      foreach ($value->get($this->getPluginDefinition()['field']) as $item) {
        yield $item->get('value')->getValue() === '1';
      }
    }
  }

}
