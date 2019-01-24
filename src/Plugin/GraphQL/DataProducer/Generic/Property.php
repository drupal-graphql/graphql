<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Generic;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DataProducer(
 *   id = "property",
 *   name = @Translation("Property"),
 *   description = @Translation("Generic producer to parse a property."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Property"),
 *     multiple = TRUE
 *   ),
 *   consumes = {
 *     "value" = @ContextDefinition("property",
 *       label = @Translation("Property")
 *     ),
 *     "value" = @ContextDefinition("object",
 *       label = @Translation("Object")
 *     )
 *   }
 * )
 */
class Property extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

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

  /**
   * @param $object
   * @param $property
   *
   * @return mixed|null
   */
  public function resolve($object, $property) {
    if (!property_exists($object, $property)) {
      return NULL;
    }
    return $object->{$property};
  }

}
