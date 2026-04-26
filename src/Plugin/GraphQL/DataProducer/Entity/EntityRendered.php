<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Component\Plugin\Definition\PluginDefinitionInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns the rendered entity in a given view mode.
 */
#[DataProducer(
  id: "entity_rendered",
  name: new TranslatableMarkup("Entity rendered"),
  description: new TranslatableMarkup("Returns the rendered entity."),
  produces: new ContextDefinition(
    data_type: "string",
    label: new TranslatableMarkup("Rendered output")
  ),
  consumes: [
    "entity" => new ContextDefinition(
      data_type: "entity",
      label: new TranslatableMarkup("Entity")
    ),
    "mode" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("View mode"),
      required: FALSE
    ),
  ],
)]
class EntityRendered extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

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
    protected EntityTypeManagerInterface $entityTypeManager,
    protected RendererInterface $renderer,
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
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
