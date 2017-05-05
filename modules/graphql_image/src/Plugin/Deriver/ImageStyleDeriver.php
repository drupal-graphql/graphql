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
    return [];
  }

}
