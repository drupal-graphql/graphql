<?php

namespace Drupal\graphql_plugin_test\Plugin\GraphQL\Fields;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Drupal\graphql_plugin_test\GarageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * List everything we've got in our garage.
 *
 * @GraphQLField(
 *   id = "garage",
 *   secure = true,
 *   name = "garage",
 *   type = "[Vehicle]"
 * )
 */
class Garage extends FieldPluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

  /**
   * The garage instance.
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
   * Garage constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition array.
   * @param \Drupal\graphql_plugin_test\GarageInterface $garage
   *   The garaga service.
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, GarageInterface $garage) {
    $this->garage = $garage;
    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    foreach ($this->garage->getVehicles() as $vehicle) {
      yield $vehicle;
    }
  }

}
