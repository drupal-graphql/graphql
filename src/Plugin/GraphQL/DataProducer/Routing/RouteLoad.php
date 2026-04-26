<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Routing;

use Drupal\Component\Plugin\Definition\PluginDefinitionInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Language\Language;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\redirect\RedirectRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns the URL of the given path.
 *
 * @todo Fix the type of the output context.
 */
#[DataProducer(
  id: "route_load",
  name: new TranslatableMarkup("Load route"),
  description: new TranslatableMarkup("Loads a route."),
  produces: new ContextDefinition(
    data_type: "any",
    label: new TranslatableMarkup("Route")
  ),
  consumes: [
    "path" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("Path")
    ),
    "language" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("Language"),
      required: FALSE,
      default_value: "und"
    ),
  ],
)]
class RouteLoad extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('path.validator'),
      $container->get('redirect.repository', ContainerInterface::NULL_ON_INVALID_REFERENCE)
    );
  }

  /**
   * Route constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $pluginId
   *   The plugin_id for the plugin instance.
   * @param \Drupal\Component\Plugin\Definition\PluginDefinitionInterface|array $pluginDefinition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Path\PathValidatorInterface $pathValidator
   *   The path validator service.
   * @param \Drupal\redirect\RedirectRepository|null $redirectRepository
   *   (optional) The redirect repository service.
   */
  public function __construct(
    array $configuration,
    string $pluginId,
    PluginDefinitionInterface|array $pluginDefinition,
    protected PathValidatorInterface $pathValidator,
    protected ?RedirectRepository $redirectRepository = NULL,
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * Resolver.
   */
  public function resolve(string $path, ?string $language, RefinableCacheableDependencyInterface $metadata): ?Url {
    $language = $language ?? Language::LANGCODE_NOT_SPECIFIED;
    $redirect = $this->redirectRepository ? $this->redirectRepository->findMatchingRedirect($path, [], $language) : NULL;
    if ($redirect !== NULL) {
      $url = $redirect->getRedirectUrl();
    }
    else {
      $url = $this->pathValidator->getUrlIfValidWithoutAccessCheck($path);
    }

    if ($url && $url->isRouted() && $url->access()) {
      return $url;
    }

    $metadata->addCacheTags(['4xx-response']);
    return NULL;
  }

}
