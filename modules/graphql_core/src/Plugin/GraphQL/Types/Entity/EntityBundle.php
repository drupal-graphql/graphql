<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Types\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase;
use Drupal\graphql\Utility\StringHelper;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Plugin for GraphQL types derived from Drupal entity bundles.
 *
 * @GraphQLType(
 *   id = "entity_bundle",
 *   weight = -1,
 *   schema_cache_tags = {"entity_types", "entity_bundles"},
 *   deriver = "Drupal\graphql_core\Plugin\Deriver\Types\EntityBundleDeriver"
 * )
 */
class EntityBundle extends TypePluginBase {

  /**
   * Returns name of the bundle.
   *
   * @param string $entityTypeId
   *   The entity type.
   * @param string $bundleId
   *   The bundle.
   *
   * @return string
   */
  public static function getId($entityTypeId, $bundleId) {
    return StringHelper::camelCase($entityTypeId, $bundleId);
  }

  /**
   * {@inheritdoc}
   */
  public function applies($object, ResolveInfo $info = NULL) {
    return $object instanceof EntityInterface
      && $object->getEntityTypeId() == $this->getPluginDefinition()['entity_type']
      && $object->bundle() == $this->getPluginDefinition()['bundle'];
  }

}
