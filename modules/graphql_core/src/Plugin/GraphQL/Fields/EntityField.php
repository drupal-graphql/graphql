<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields;

use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Drupal\graphql\Utility\StringHelper;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Generic field plugin for rendering entity fields.
 *
 * @GraphQLField(
 *   id = "entity_field",
 *   secure = true,
 *   nullable = true,
 *   weight = -2,
 *   deriver = "Drupal\graphql_core\Plugin\Deriver\EntityFieldDeriver",
 * )
 */
class EntityField extends FieldPluginBase {

  /**
   * Returns a string if for the plugin.
   *
   * @param string $fieldName
   *   Field id.
   *
   * @return string
   */
  public static function getId($fieldName) {
    return StringHelper::propCase([$fieldName]);
  }

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
