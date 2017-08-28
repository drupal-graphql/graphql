<?php

namespace Drupal\graphql_json\Plugin\GraphQL\Fields;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Drupal\text\Plugin\Field\FieldType\TextItemBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Expose text fields as json objects.
 *
 * @GraphQLField(
 *   id = "json_field",
 *   secure = true,
 *   field_formatter = "graphql_json",
 *   type = "JsonNode",
 *   deriver = "Drupal\graphql_content\Plugin\Deriver\FieldFormatterDeriver"
 * )
 */
class JsonField extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof ContentEntityInterface) {
      foreach ($value->get($this->getPluginDefinition()['field']) as $field) {
        if ($field instanceof TextItemBase) {
          yield json_decode($field->getValue()['value'], TRUE);
        }
      }
    }
  }

}
