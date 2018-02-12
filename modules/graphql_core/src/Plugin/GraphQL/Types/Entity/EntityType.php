<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Types\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * @GraphQLType(
 *   id = "entity_type",
 *   schema_cache_tags = {"entity_types"},
 *   deriver = "Drupal\graphql_core\Plugin\Deriver\Types\EntityTypeDeriver"
 * )
 */
class EntityType extends TypePluginBase {

  /**
   * {@inheritdoc}
   */
  public function applies($object, ResolveInfo $info = NULL) {
    if ($object instanceof EntityInterface) {
      $definition = $this->getPluginDefinition();
      return $object->getEntityTypeId() === $definition['entity_type'];
    }

    return FALSE;
  }

}
