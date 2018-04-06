<?php

namespace Drupal\graphql_plugin_test\Plugin\GraphQL\Mutations;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Mutations\MutationPluginBase;
use Drupal\graphql_plugin_test\GarageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * A test mutation.
 *
 * @GraphQLMutation(
 *   id = "buy_car",
 *   secure = true,
 *   name = "buyCar",
 *   type = "Car",
 *   arguments = {
 *     "car" = "CarInput!"
 *   }
 * )
 */
class BuyCar extends MutationPluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

  /**
   * The garage.
   *
   * @var \Drupal\graphql_plugin_test\GarageInterface
   */
  protected $garage;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static($configuration, $pluginId, $pluginDefinition, $container->get('graphql_test.garage'));
  }

  /**
   * BuyCar constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition array.
   * @param \Drupal\graphql_plugin_test\GarageInterface $garage
   *   The garage service.
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, GarageInterface $garage) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->garage = $garage;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveContext $context, ResolveInfo $info) {
    return $this->garage->insertVehicle($args['car']);
  }

}
