<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields;

use Drupal\Core\Field\FieldItemBase;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Generic field plugin for rendering entity field properties.
 *
 * @GraphQLField(
 *   id = "entity_field_item",
 *   secure = true,
 *   nullable = true,
 *   weight = -1,
 *   deriver = "Drupal\graphql_core\Plugin\Deriver\EntityFieldItemDeriver",
 * )
 */
class EntityFieldItem extends FieldPluginBase {

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
