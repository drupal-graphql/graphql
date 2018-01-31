<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Entity;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\graphql\GraphQL\Cache\CacheableValue;
use Drupal\graphql_core\Plugin\GraphQL\Fields\EntityFieldBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Generic field plugin for rendering entity fields.
 *
 * @GraphQLField(
 *   id = "entity_field",
 *   secure = true,
 *   weight = -2,
 *   deriver = "Drupal\graphql_core\Plugin\Deriver\Fields\EntityFieldDeriver",
 * )
 */
class EntityField extends EntityFieldBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof FieldableEntityInterface) {
      $fieldName = $this->getPluginDefinition()['field'];
      if ($value->hasField($fieldName)) {
        /** @var \Drupal\Core\Field\FieldItemListInterface $items */
        $items = $value->get($fieldName);

        if (($access = $items->access('view', NULL, TRUE)) && $access->isAllowed()) {
          foreach ($items as $item) {
            if (!empty($this->getPluginDefinition()['property'])) {
              yield new CacheableValue($this->resolveItem($item, $args, $info), [$access]);
            }
            else {
              yield new CacheableValue($item, [$access]);
            }
          }
        }
      }
    }
  }

}
