<?php

namespace Drupal\graphql\Plugin\GraphQL\Subscriptions;

use Drupal\Component\Plugin\PluginBase;
use Drupal\graphql\Plugin\SubscriptionPluginInterface;
use Drupal\graphql\Plugin\SubscriptionPluginManager;
use Drupal\graphql\Plugin\GraphQL\Traits\ArgumentAwarePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\DeprecatablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\DescribablePluginTrait;
use Drupal\graphql\Plugin\GraphQL\Traits\TypedPluginTrait;
use Drupal\graphql\Plugin\SchemaBuilderInterface;

abstract class SubscriptionPluginBase extends PluginBase implements SubscriptionPluginInterface {
  use TypedPluginTrait;
  use DescribablePluginTrait;
  use ArgumentAwarePluginTrait;
  use DeprecatablePluginTrait;

  /**
   * {@inheritdoc}
   */
  public static function createInstance(SchemaBuilderInterface $builder, SubscriptionPluginManager $manager, $definition, $id) {
    return [
      'description' => $definition['description'],
      'deprecationReason' => $definition['deprecationReason'],
      'type' => $builder->processType($definition['type']),
      'args' => $builder->processArguments($definition['args']),
      'resolve' => function ($value, $args, $context, $info) use ($manager, $id) {
        $instance = $manager->getInstance(['id' => $id]);
        return call_user_func_array([$instance, 'resolve'], [$value, $args, $context, $info]);
      },
    ];
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
      'deprecationReason' => $this->buildDeprecationReason($definition),
    ];
  }
}
