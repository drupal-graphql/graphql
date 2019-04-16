<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity\Fields\Image;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\file\FileInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DataProducer(
 *   id = "image_url",
 *   name = @Translation("Image URL"),
 *   description = @Translation("Returns the url of an image entity."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("URL")
 *   ),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity")
 *     )
 *   }
 * )
 */
class ImageUrl extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('renderer')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(array $configuration, string $plugin_id, array $plugin_definition, RendererInterface $renderer) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->renderer = $renderer;
  }

  /**
   * @param \Drupal\file\FileInterface $entity
   *   The file entity.
   *
   * @return mixed
   *   The file url.
   */
  public function resolve(FileInterface $entity, RefinableCacheableDependencyInterface $metadata) {
    $access = $entity->access('view', NULL, TRUE);
    $metadata->addCacheableDependency($access);
    if ($access->isAllowed()) {
      $context = new RenderContext();
      $file_url = $this->renderer->executeInRenderContext($context, function () use ($entity, $image_style, $dimensions) {
        return file_create_url($entity->getFileUri());
      });
      return $file_url;
    }
  }

}
