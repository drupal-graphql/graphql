<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Generic;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DataProducer(
 *   id = "object",
 *   name = @Translation("Object"),
 *   description = @Translation("Generic producer to parse an object."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Object"),
 *     multiple = TRUE
 *   ),
 *   consumes = {
 *     "value" = @ContextDefinition("value",
 *       label = @Translation("Value")
 *     )
 *   }
 * )
 */
class Object extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition
    );
  }

  /**
   * EntityLoad constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param array $pluginDefinition
   *   The plugin definition array.
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    $configuration,
    $pluginId,
    $pluginDefinition
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }

  public function resolve($value) {
    return $value;
  }
}
