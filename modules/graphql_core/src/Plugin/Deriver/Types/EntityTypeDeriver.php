<?php

namespace Drupal\graphql_core\Plugin\Deriver\Types;

use Drupal\Core\Entity\ContentEntityTypeInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\graphql\Utility\StringHelper;
use Drupal\graphql_core\Plugin\Deriver\EntityTypeDeriverBase;

class EntityTypeDeriver extends EntityTypeDeriverBase {
  use StringTranslationTrait;

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    foreach ($this->entityTypeManager->getDefinitions() as $typeId => $type) {
      if (!($type instanceof ContentEntityTypeInterface)) {
        continue;
      }

      // Create the entity type only for types that do not support bundles. For
      // all others, we create common interfaces instead.
      if ($type->hasKey('bundle')) {
        continue;
      }

      $derivative = [
        'name' => StringHelper::camelCase($typeId),
        'description' => $this->t("The '@type' entity type.", [
          '@type' => $type->getLabel(),
        ]),
        'type' => "entity:$typeId",
        'interfaces' => $this->getInterfaces($type, $basePluginDefinition),
        'entity_type' => $typeId,
      ] + $basePluginDefinition;

      $this->derivatives[$typeId] = $derivative;
    }

    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
