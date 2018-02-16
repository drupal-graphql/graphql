<?php

namespace Drupal\graphql\Plugin\GraphQL\Mutations;

use Drupal\Component\Plugin\PluginBase;
use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilder;
use Drupal\graphql\Plugin\GraphQL\Traits\ArgumentAwarePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\CacheablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\DeprecatablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\DescribablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\TypedPluginTrait;
use Drupal\graphql\Plugin\GraphQL\TypeSystemPluginInterface;

abstract class MutationPluginBase extends PluginBase implements TypeSystemPluginInterface {
  use CacheablePluginTrait;
  use TypedPluginTrait;
  use DescribablePluginTrait;
  use ArgumentAwarePluginTrait;
  use DeprecatablePluginTrait;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(PluggableSchemaBuilder $builder, $definition, $id) {
    return [
      'args' => $builder->resolveArgs($definition['args']),
      'resolve' => function ($args) use ($builder, $id) {
        $instance = $builder->getPluginInstance(GRAPHQL_MUTATION_PLUGIN, $id);
        return call_user_func_array([$instance, 'resolve'], $args);
      },
    ] + $definition;
  }

  /**
   * {@inheritdoc}
   */
  public function getDefinition() {
    $definition = $this->getPluginDefinition();

    return [
      'type' => $this->buildType($definition),
      'description' => $this->buildDescription($definition),
      'args' => $this->buildArguments($definition),
      'deprecationReason' => $this->buildDeprecationReason($definition)
    ];
  }

}
