<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Entity;

use Drupal\Core\Entity\EntityDescriptionInterface;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * @GraphQLField(
 *   id = "entity_description",
 *   secure = true,
 *   name = "entityDescription",
 *   type = "String",
 *   parents = {"EntityDescribable"}
 * )
 */
class EntityDescription extends FieldPluginBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof EntityDescriptionInterface) {
      yield $value->getDescription();
    }
  }

}
