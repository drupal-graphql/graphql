<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Routing;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * TODO: Fix the type of the output context.
 *
 * @DataProducer(
 *   id = "route_load",
 *   name = @Translation("Load route"),
 *   description = @Translation("Loads a route."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Route")
 *   ),
 *   consumes = {
 *     "path" = @ContextDefinition("string",
 *       label = @Translation("Path")
 *     )
 *   }
 * )
 */
class RouteLoad extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The path validator service.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('path.validator')
    );
  }

  /**
   * Route constructor.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition.
   * @param \Drupal\Core\Path\PathValidatorInterface $pathValidator
   *   The path validator service.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    PathValidatorInterface $pathValidator
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->pathValidator = $pathValidator;
  }

  /**
   * @param $path
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   *
   * @return \Drupal\Core\Url|null
   */
  public function resolve($path, RefinableCacheableDependencyInterface $metadata) {
    if (($url = $this->pathValidator->getUrlIfValidWithoutAccessCheck($path)) && $url->isRouted() && $url->access()) {
      return $url;
    }

    $metadata->addCacheTags(['4xx-response']);
    return NULL;
  }

}
