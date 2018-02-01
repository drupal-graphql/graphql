<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Entity\Config;

use Drupal\graphql_core\Plugin\GraphQL\Fields\EntityFieldBase;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Type\Scalar\AbstractScalarType;

/**
 * @GraphQLField(
 *   id = "config_entity_property",
 *   secure = true,
 *   nullable = true,
 *   weight = -2,
 *   deriver = "Drupal\graphql_core\Plugin\Deriver\Fields\ConfigEntityPropertyDeriver",
 * )
 */
class ConfigEntityProperty extends EntityFieldBase {

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    $definition = $this->getPluginDefinition();
    $property = $definition['property'];
    $result = $value->get($property);
    if (($type = $info->getReturnType()->getNamedType()) && $type instanceof AbstractScalarType) {
      $result = $type->serialize($result);
    }

    yield $result;
  }

}
