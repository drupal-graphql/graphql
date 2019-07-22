<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\Fields\Image;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\file\FileInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\image\Entity\ImageStyle;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DataProducer(
 *   id = "image_derivative",
 *   name = @Translation("Image Derivative"),
 *   description = @Translation("Returns an image derivative."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Image derivative properties")
 *   ),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity"),
 *       required = FALSE
 *     ),
 *     "style" = @ContextDefinition("string",
 *       label = @Translation("Image style")
 *     )
 *   }
 * )
 */
class ImageDerivative extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

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
      $container->get('renderer')
    );
  }

  /**
   * EntityRendered constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    RendererInterface $renderer
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->renderer = $renderer;
  }

  /**
   * @param \Drupal\file\FileInterface $entity
   *   The file entity received.
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   *   The metadata object to add caching to objects.
   *
   * @param $style
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   *
   * @return mixed
   *   The image url and dimensions for the image style .
   */
  public function resolve(FileInterface $entity = NULL, $style, RefinableCacheableDependencyInterface $metadata) {
    // Return if we dont have an entity.
    if (!$entity) {
      return NULL;
    }

    $access = $entity->access('view', NULL, TRUE);
    $metadata->addCacheableDependency($access);
    if ($access->isAllowed() && $image_style = ImageStyle::load($style)) {
      // Determine the dimensions of the styled image.
      $dimensions = [
        'width' => $entity->width,
        'height' => $entity->height,
      ];

      $image_style->transformDimensions($dimensions, $entity->getFileUri());
      $metadata->addCacheableDependency($image_style);

      $context = new RenderContext();
      $image = $this->renderer->executeInRenderContext($context, function () use ($entity, $image_style, $dimensions) {
        return [
          'url' => $image_style->buildUrl($entity->getFileUri()),
          'width' => $dimensions['width'],
          'height' => $dimensions['height'],
        ];
      });
      return $image;
    }

    return NULL;
  }
}
