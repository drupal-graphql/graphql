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
    $this->derivatives = [];
    $styles = array_keys(ImageStyle::loadMultiple());
    $styles[] = 'original';
    foreach ($styles as $style) {
      $this->derivatives[$style] = [
        'id' => $style,
        'name' => graphql_core_propcase($style) . 'Image',
        'image_style' => $style,
      ] + $basePluginDefinition;
    }
    return parent::getDerivativeDefinitions($basePluginDefinition);
  }

}
