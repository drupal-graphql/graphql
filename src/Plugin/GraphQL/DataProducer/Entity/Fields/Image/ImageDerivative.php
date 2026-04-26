<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\Fields\Image;

use Drupal\Component\Plugin\Definition\PluginDefinitionInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\file\FileInterface;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\image\Entity\ImageStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns an image style derivative of an image.
 */
#[DataProducer(
  id: "image_derivative",
  name: new TranslatableMarkup("Image Derivative"),
  description: new TranslatableMarkup("Returns an image derivative."),
  produces: new ContextDefinition(
    data_type: "any",
    label: new TranslatableMarkup("Image derivative properties")
  ),
  consumes: [
    "entity" => new ContextDefinition(
      data_type: "entity",
      label: new TranslatableMarkup("Entity"),
      required: FALSE
    ),
    "style" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("Image style")
    ),
  ]
)]
class ImageDerivative extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

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
      $container->get('image.factory'),
      $container->get('renderer'),
    );
  }

  /**
   * ImageDerivative constructor.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $pluginId
   *   The plugin_id for the plugin instance.
   * @param \Drupal\Component\Plugin\Definition\PluginDefinitionInterface|array $pluginDefinition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Image\ImageFactory $imageFactory
   *   The image factory service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(
    array $configuration,
    string $pluginId,
    PluginDefinitionInterface|array $pluginDefinition,
    protected ImageFactory $imageFactory,
    protected RendererInterface $renderer,
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * Resolver.
   *
   * @return array|null
   */
  public function resolve(?FileInterface $entity, string $style, RefinableCacheableDependencyInterface $metadata): ?array {
    // Return if we don't have an entity or if it is an SVG image where image
    // styles don't apply.
    if (!$entity || $entity->getMimeType() == 'image/svg+xml') {
      return NULL;
    }

    $access = $entity->access('view', NULL, TRUE);
    $metadata->addCacheableDependency($access);
    if ($access->isAllowed() && $image_style = ImageStyle::load($style)) {
      /** @var \Drupal\Core\Image\ImageInterface $image */
      $image = $this->imageFactory->get($entity->getFileUri());
      if ($image->isValid()) {
        $width = $image->getWidth();
        $height = $image->getHeight();
      }

      // Determine the dimensions of the styled image.
      $dimensions = [
        'width' => $width ?? 0,
        'height' => $height ?? 0,
      ];

      $image_style->transformDimensions($dimensions, $entity->getFileUri());
      $metadata->addCacheableDependency($image_style);

      // The underlying URL generator that will be invoked will leak cache
      // metadata, resulting in an exception. By wrapping within a new render
      // context, we can capture the leaked metadata and make sure it gets
      // incorporated into the response.
      $context = new RenderContext();
      $url = $this->renderer->executeInRenderContext($context, function () use ($image_style, $entity) {
        return $image_style->buildUrl($entity->getFileUri());
      });

      if (!$context->isEmpty()) {
        $metadata->addCacheableDependency($context->pop());
      }

      return [
        'url' => $url,
        'width' => $dimensions['width'],
        'height' => $dimensions['height'],
      ];
    }

    return NULL;
  }

}
