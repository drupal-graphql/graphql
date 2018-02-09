<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Types\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Plugin for GraphQL types derived from Drupal entity bundles.
 *
 * @GraphQLType(
 *   id = "entity_bundle",
 *   schema_cache_tags = {"entity_types", "entity_bundles"},
 *   deriver = "Drupal\graphql_core\Plugin\Deriver\Types\EntityBundleDeriver"
 * )
 */
class EntityBundle extends TypePluginBase {

  /**
   * {@inheritdoc}
   */
  public function applies($object, ResolveInfo $info = NULL) {
    return $object instanceof EntityInterface
      && $object->getEntityTypeId() == $this->getPluginDefinition()['entity_type']
      && $object->bundle() == $this->getPluginDefinition()['entity_bundle'];
  }

}
