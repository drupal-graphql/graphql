<?php

namespace Drupal\graphql_content\Plugin\GraphQL\Fields;


use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Generic field plugin for rendering entity fields to string values.
 *
 * @GraphQLField(
 *   id = "raw_field_value",
 *   nullable = true,
 *   weight = -1,
 *   type = "String",
 *   field_formatter = "raw_value",
 *   deriver = "Drupal\graphql_content\Plugin\Deriver\RawValueFieldDeriver"
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
        yield $value->get($fieldName)->getValue();
      }
    }
  }

}
