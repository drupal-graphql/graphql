<?php

namespace Drupal\graphql_core\Plugin\Deriver\Types;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\graphql\Utility\StringHelper;
use Drupal\graphql_core\Plugin\Deriver\EntityTypeDeriverBase;

/**
 * Derive GraphQL Interfaces from Drupal entity types.
 */
class EntityBundleDeriver extends EntityTypeDeriverBase {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    $bundles = $this->entityTypeBundleInfo->getAllBundleInfo();
    foreach ($this->entityTypeManager->getDefinitions() as $typeId => $type) {
      if (!($type instanceof ContentEntityTypeInterface)) {
        continue;
      }

      // Only create a bundle type for entity types that support bundles.
      if (!$type->hasKey('bundle') || !array_key_exists($typeId, $bundles)) {
        continue;
      }

      foreach ($bundles[$typeId] as $bundle => $bundleDefinition) {
        $derivative = [
          'name' => StringHelper::camelCase($typeId, $bundle),
          'description' => $this->t("The '@bundle' bundle of the '@type' entity type.", [
            '@bundle' => $bundleDefinition['label'],
            '@type' => $type->getLabel(),
          ]),
          'interfaces' => [StringHelper::camelCase($typeId)],
          'type' => "entity:$typeId:$bundle",
          'entity_type' => $typeId,
          'entity_bundle' => $bundle,
        ] + $basePluginDefinition;

        if ($typeId === 'node') {
          // TODO: Make this more generic somehow.
          $derivative['response_cache_contexts'][] = 'user.node_grants:view';
        }

        $this->derivatives["$typeId-$bundle"] = $derivative;
      }
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
