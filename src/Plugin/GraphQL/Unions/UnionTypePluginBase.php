<?php

namespace Drupal\graphql\Plugin\GraphQL\Unions;

use Drupal\Component\Plugin\PluginBase;
use Drupal\graphql\Plugin\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\DescribablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface;

/**
 * Base class for GraphQL union type plugins.
 */
abstract class UnionTypePluginBase extends PluginBase implements TypeSystemPluginInterface {
  use CacheablePluginTrait;
  use DescribablePluginTrait;

  /**
   * {@inheritdoc}
   */
  public function getDefinition() {
    $definition = $this->getPluginDefinition();

    return [
      'description' => $this->buildDescription($definition),
      'types' => $this->buildTypes($definition),
    ];
  }

  /**
   * @param $definition
   *
   * @return mixed
   */
  protected function buildTypes($definition) {
    return $definition['types'];
  }

}
