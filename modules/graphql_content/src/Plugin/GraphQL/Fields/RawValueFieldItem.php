<?php

namespace Drupal\graphql_content\Plugin\GraphQL\Fields;

use Drupal\Core\Field\FieldItemBase;
use Drupal\Core\Field\Plugin\Field\FieldType\BooleanItem;
use Drupal\Core\Field\Plugin\Field\FieldType\DecimalItem;
use Drupal\Core\Field\Plugin\Field\FieldType\FloatItem;
use Drupal\Core\Field\Plugin\Field\FieldType\IntegerItem;
use Drupal\Core\Field\Plugin\Field\FieldType\TimestampItem;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Generic field plugin for rendering entity fields to string values.
 *
 * @GraphQLField(
 *   id = "raw_field_item",
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
      $column = $this->getPluginDefinition()['schema_column'];
      $item = $value->getValue();
      $result = $item[$column];

      if ($value instanceof BooleanItem || $value instanceof IntegerItem || $value instanceof TimestampItem) {
        $result = (int) $result;
      }
      elseif ($value instanceof FloatItem || $value instanceof DecimalItem) {
        $result = (float) $result;
      }

      yield $result;
    }
  }

}
