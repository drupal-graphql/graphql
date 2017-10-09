<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Types;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\Utility\StringHelper;
use Drupal\graphql_core\GraphQL\TypePluginBase;

/**
 * Plugin for GraphQL types derived from Drupal entity bundles.
 *
 * @GraphQLType(
 *   id = "entity_bundle",
 *   weight = -1,
 *   cache_tags = {"entity_types", "entity_bundles"},
 *   deriver = "Drupal\graphql_core\Plugin\Deriver\EntityBundleDeriver"
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
    return StringHelper::camelCase([$entityTypeId, $bundleId]);
  }

  /**
   * {@inheritdoc}
   */
  public function applies($value) {
    return $value instanceof EntityInterface
      && $value->getEntityTypeId() == $this->getPluginDefinition()['entity_type']
      && $value->bundle() == $this->getPluginDefinition()['bundle'];
  }

}
