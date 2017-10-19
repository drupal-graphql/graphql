<?php

namespace Drupal\graphql_image\Plugin\GraphQL\Fields;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Drupal\image\Plugin\Field\FieldType\ImageItem;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the responsive image.
 *
 * @GraphQLField(
 *   id = "image_responsive",
 *   secure = true,
 *   name = "responsive",
 *   type = "String",
 *   nullable = true,
 *   parents = {"Image"},
 *   arguments = {
 *     "style" = "ResponsiveImageStyleId"
 *   },
 *   deriver = "Drupal\graphql_image\Plugin\Deriver\ImageResponsiveDeriver"
 * )
 */
class ImageResponsive extends FieldPluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

  /**
   * Renderer instance to render fields.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs an ImageResponsive object.
   *
   * @param array $configuration
   *   Plugin configuration.
   * @param string $pluginId
   *   Id of the plugin.
   * @param array $pluginDefinition
   *   Plugin definition array.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, RendererInterface $renderer) {
    $this->renderer = $renderer;
    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * {@inheritdoc}
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
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof ImageItem && $value->entity->access('view')) {
      $variables = [
        '#theme' => 'responsive_image',
        '#responsive_image_style_id' => $args['style'],
        '#uri' => $value->entity->getFileUri(),
      ];

      yield $this->renderer->renderRoot($variables);
    }
  }

}
