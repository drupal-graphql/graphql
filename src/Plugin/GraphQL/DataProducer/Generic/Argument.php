<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Generic;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use GraphQL\Type\Definition\ResolveInfo;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DataProducer(
 *   id = "argument",
 *   name = @Translation("Argument"),
 *   description = @Translation("Generic producer to parse an argument."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Argument"),
 *     multiple = TRUE
 *   ),
 *   consumes = {
 *     "value" = @ContextDefinition("argument",
 *       label = @Translation("Argument")
 *     ),
 *     "value" = @ContextDefinition("value",
 *       label = @Translation("Value")
 *     ),
 *     "string" = @ContextDefinition("string",
 *       label = @Translation("String")
 *     )
 *   }
 * )
 */
class Argument extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

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

  public function __invoke($value, $args, ResolveContext $context, ResolveInfo $info) {
    return function ($value, $args, ResolveContext $context, ResolveInfo $info) use ($name) {
      return $args[$name] ?? NULL;
    };
  }

}
