<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Entity;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql_core\Plugin\GraphQL\Fields\EntityFieldBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * @GraphQLField(
 *   id = "entity_field_item",
 *   secure = true,
 *   weight = -1,
 *   deriver = "Drupal\graphql_core\Plugin\Deriver\Fields\EntityFieldItemDeriver",
 * )
 */
class EntityFieldItem extends EntityFieldBase {

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    yield $this->resolveItem($value, $args, $context, $info);
  }

}
