<?php

namespace Drupal\graphql_content\Plugin\GraphQL\Fields;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Generic field plugin for rendering entity fields to string values.
 *
 * @GraphQLField(
 *   id = "raw_field",
 *   nullable = true,
 *   type = "String",
 *   deriver = "Drupal\graphql_content\Plugin\Deriver\RawValueFieldDeriver",
 *   field_formatter = "graphql_raw_value"
 * )
 */
class RawValueField extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof FieldableEntityInterface) {
      $fieldName = $this->getPluginDefinition()['field'];
      if ($value->hasField($fieldName)) {
        foreach ($value->get($fieldName) as $item) {
          yield $item;
        }
      }
    }
  }

}
