<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\Fields\Image;

use Drupal\Component\Plugin\Definition\PluginDefinitionInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\file\FileInterface;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns the file URL of a file entity.
 */
#[DataProducer(
  id: "image_url",
  name: new TranslatableMarkup("Image URL"),
  description: new TranslatableMarkup("Returns the url of an image entity."),
  produces: new ContextDefinition(
    data_type: "string",
    label: new TranslatableMarkup("URL"),
  ),
  consumes: [
    "entity" => new ContextDefinition(
      data_type: "entity",
      label: new TranslatableMarkup("Entity"),
    ),
  ],
)]
class ImageUrl extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('renderer'),
      $container->get('file_url_generator')
    );
  }

  /**
   * ImageUrl constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $pluginId
   *   The plugin_id for the plugin instance.
   * @param \Drupal\Component\Plugin\Definition\PluginDefinitionInterface|array $pluginDefinition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $fileUrlGenerator
   *   The file URL generator service.
   */
  public function __construct(
    array $configuration,
    string $pluginId,
    PluginDefinitionInterface|array $pluginDefinition,
    protected RendererInterface $renderer,
    protected FileUrlGeneratorInterface $fileUrlGenerator,
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * Resolver.
   */
  public function resolve(FileInterface $entity, RefinableCacheableDependencyInterface $metadata): ?string {
    $access = $entity->access('view', NULL, TRUE);
    $metadata->addCacheableDependency($access);
    if ($access->isAllowed()) {
      // The underlying URL generator that will be invoked will leak cache
      // metadata, resulting in an exception. By wrapping within a new render
      // context, we can capture the leaked metadata and make sure it gets
      // incorporated into the response.
      $context = new RenderContext();
      $url = $this->renderer->executeInRenderContext($context, function () use ($entity) {
        return $this->fileUrlGenerator->generateAbsoluteString($entity->getFileUri());
      });

      if (!$context->isEmpty()) {
        $metadata->addCacheableDependency($context->pop());
      }
      return $url;
    }
    return NULL;
  }

}
