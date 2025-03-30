<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Component\Plugin\Definition\PluginDefinitionInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns the rendered entity in a given view mode.
 *
 * @DataProducer(
 *   id = "entity_rendered",
 *   name = @Translation("Entity rendered"),
 *   description = @Translation("Returns the rendered entity."),
 *   produces = @ContextDefinition("string",
 *     label = @Translation("Rendered output")
 *   ),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Entity")
 *     ),
 *     "mode" = @ContextDefinition("string",
 *       label = @Translation("View mode"),
 *       required = FALSE
 *     )
 *   }
 * )
 */
class EntityRendered extends DataProducerPluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

  /**
   * The entity type manager service.
   */
  protected EntityTypeManagerInterface $entityTypeManager;

  /**
   * The renderer service.
   */
  protected RendererInterface $renderer;

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
      $container->get('entity_type.manager'),
      $container->get('renderer')
    );
  }

  /**
   * EntityRendered constructor.
   */
  public function __construct(
    array $configuration,
    string $pluginId,
    PluginDefinitionInterface|array $pluginDefinition,
    EntityTypeManagerInterface $entityTypeManager,
    RendererInterface $renderer,
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->entityTypeManager = $entityTypeManager;
    $this->renderer = $renderer;
  }

  /**
   * Resolver.
   */
  public function resolve(EntityInterface $entity, ?string $mode, RefinableCacheableDependencyInterface $metadata): string {
    $mode = $mode ?? 'full';
    $builder = $this->entityTypeManager->getViewBuilder($entity->getEntityTypeId());
    $view = $builder->view($entity, $mode, $entity->language()->getId());

    $context = new RenderContext();
    $result = $this->renderer->executeInRenderContext($context, function () use ($view) {
      return $this->renderer->render($view);
    });

    if (!$context->isEmpty()) {
      $metadata->addCacheableDependency($context->pop());
    }

    return (string) $result;
  }

}
