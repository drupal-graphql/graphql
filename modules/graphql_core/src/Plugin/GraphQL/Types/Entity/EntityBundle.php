<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Types\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
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
  public function applies($object, ResolveContext $context, ResolveInfo $info) {
    if ($object instanceof EntityInterface) {
      $definition = $this->getPluginDefinition();
      if ($object->getEntityTypeId() === $definition['entity_type']) {
        return $object->bundle() === $definition['entity_bundle'];
      }
    }

    return FALSE;
  }

}
