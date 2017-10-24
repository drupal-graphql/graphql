<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Entity;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\Plugin\DataType\FieldItem;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Drupal\graphql\Utility\StringHelper;
use Drupal\graphql_core\Plugin\GraphQL\Fields\EntityFieldBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Generic field plugin for rendering entity fields.
 *
 * @GraphQLField(
 *   id = "entity_field",
 *   secure = true,
 *   nullable = true,
 *   weight = -2,
 *   deriver = "Drupal\graphql_core\Plugin\Deriver\Fields\EntityFieldDeriver",
 * )
 */
class EntityField extends EntityFieldBase {

  /**
   * Returns a string id for the plugin.
   *
   * @param string $fieldName
   *   Field id.
   *
   * @return string
   */
  public static function getId($fieldName) {
    return StringHelper::propCase($fieldName);
  }

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof FieldableEntityInterface) {
      $fieldName = $this->getPluginDefinition()['field'];
      if ($value->hasField($fieldName)) {
        /** @var \Drupal\Core\Field\FieldItemBase $item */
        foreach ($value->get($fieldName) as $item) {
          $properties = $item->getProperties(TRUE);
          if (count($properties) === 1) {
            yield $this->resolveItem($item);
          }
          else {
            yield $item;
          }
        }
      }
    }
  }

}
