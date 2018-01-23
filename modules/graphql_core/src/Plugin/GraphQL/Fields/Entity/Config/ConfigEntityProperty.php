<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Entity\Config;

use Drupal\graphql_core\Plugin\GraphQL\Fields\EntityFieldBase;
use Youshido\GraphQL\Execution\ResolveInfo;

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
    yield $value->get($property);
  }

}
