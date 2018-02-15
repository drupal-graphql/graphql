<?php

namespace Drupal\graphql\GraphQL;

use Youshido\GraphQL\Config\Field\FieldConfig;

interface ComplexFieldInterface {

  /**
   * Calculates the complexity cost of this field.
   *
   * @param array $args
   *   The field arguments array.
   * @param \Youshido\GraphQL\Config\Field\FieldConfig $fieldConfig
   *   The field config object.
   * @param int $childScore
   *   The child score.
   *
   * @return int
   *   The complexity cost of this field.
   */
  public static function calculateCost(array $args, FieldConfig $fieldConfig, $childScore = 0);

}