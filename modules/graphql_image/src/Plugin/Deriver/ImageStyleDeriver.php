<?php

namespace Drupal\graphql_image\Plugin\Deriver;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\image\Entity\ImageStyle;

/**
 * Generate GraphQLField plugins for all fields of a certain type.
 */
class ImageStyleDeriver extends DeriverBase {

  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($basePluginDefinition) {
    $this->derivatives['original'] = [
      'id' => 'original',
      'name' => 'originalImage',
      'image_style' => 'original',
    ] + $basePluginDefinition;

    /** @var \Drupal\image\ImageStyleInterface[] $styles */
    $styles = ImageStyle::loadMultiple();
    foreach ($styles as $id => $style) {
      $this->derivatives[$id] = [
        'id' => $id,
        'name' => graphql_core_propcase($id) . 'Image',
        'image_style' => $id,
        'cache_tags' => $style->getCacheTags(),
        'cache_contexts' => $style->getCacheContexts(),
        'cache_max_age' => $style->getCacheMaxAge(),
      ] + $basePluginDefinition;
    }
    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
