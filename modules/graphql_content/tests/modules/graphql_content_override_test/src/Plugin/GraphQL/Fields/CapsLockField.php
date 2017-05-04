<?php

namespace Drupal\graphql_content_override_test\Plugin\GraphQL\Fields;

use Drupal\graphql_content\Plugin\GraphQL\Fields\RenderedField;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Field override to implode multi value fields.
 *
 * @GraphQLField(
 *   id = "capslock",
 *   field_formatter = "text_default",
 *   type = "String",
 *   deriver = "\Drupal\graphql_content\Plugin\Deriver\FieldFormatterDeriver"
 * )
 */
class CapsLockField extends RenderedField {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    foreach (parent::resolveValues($value, $args, $info) as $result) {
      yield strtoupper($result);
    }
  }

}
