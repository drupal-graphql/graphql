<?php

namespace Drupal\graphql\Plugin\GraphQL\Scalars;

use Drupal\Component\Plugin\PluginBase;
use Drupal\graphql\Plugin\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface;

abstract class ScalarPluginBase extends PluginBase implements TypeSystemPluginInterface {
  use CacheablePluginTrait;

  /**
   * The type instance.
   *
   * @var \Youshido\GraphQL\Type\Scalar\AbstractScalarType
   */
  protected $definition;

}
