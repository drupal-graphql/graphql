<?php

namespace Drupal\graphql_rules\Plugin\GraphQL\Mutations;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Utility\StringHelper;
use Drupal\graphql_core\GraphQL\MutationPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

// TODO Deriver needs to set the type. (type = "EntityCrudOutput",)

// TODO What does secure do? (secure = true,)

// TODO Which cache tags should rules action be assigned to? (cache_tags = {"entity_types", "entity_bundles"},)

/**
 * Create an entity.
 *
 * @GraphQLMutation(
 *   id = "rules_action",
 *   nullable = false,
 *   deriver = "\Drupal\graphql_rules\Plugin\Deriver\RulesMutationDeriver"
 * )
 */
class RulesMutation extends MutationPluginBase implements ContainerFactoryPluginInterface {

  /**
   * Returns a camel case id for the input type associated with given action.
   *
   * @param \Drupal\rules\Entity\RulesComponentConfig $rulesComponent
   *   The action plugin.
   *
   * @return string
   *   Camel case id.
   */
  public static function getInputId($rulesComponent) {
    return static::getId($rulesComponent, 'input');
  }

  /**
   * Returns a camel case id for given rules action.
   *
   * @param \Drupal\rules\Entity\RulesComponentConfig $rulesComponent
   *   The action plugin.
   * @param string $suffix
   *   Optional suffix for sub-ids.
   *
   * @return string
   *   Camel case id.
   */
  public static function getId($rulesComponent, $suffix = '') {
    return StringHelper::camelCase([$rulesComponent->id(), $suffix]);
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition
    );
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveInfo $info) {

  }

}
