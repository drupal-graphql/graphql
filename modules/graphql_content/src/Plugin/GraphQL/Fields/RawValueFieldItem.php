<?php

namespace Drupal\graphql_content\Plugin\GraphQL\Fields;

use Drupal\Core\Field\FieldItemBase;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Generic field plugin for rendering entity fields to string values.
 *
 * @GraphQLField(
 *   id = "raw_field_item",
 *   secure = true,
 *   nullable = true,
 *   weight = -1,
 *   deriver = "Drupal\graphql_content\Plugin\Deriver\RawValueFieldItemDeriver",
 *   field_formatter = "graphql_raw_value"
 * )
 */
class RawValueFieldItem extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof FieldItemBase) {
      $definition = $this->getPluginDefinition();
      $property = $definition['property'];
      $type = $definition['type'];
      $result = $value->$property;

      if ($type == 'Int') {
        $result = (int) $result;
      }
      elseif ($type == 'Float') {
        $result = (float) $result;
      }

      yield $result;
    }
  }

}
