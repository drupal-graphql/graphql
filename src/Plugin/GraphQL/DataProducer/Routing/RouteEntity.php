<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Routing;

use Drupal\Component\Plugin\Definition\PluginDefinitionInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\TranslatableInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\GraphQL\Buffers\EntityBuffer;
use Drupal\graphql\GraphQL\Buffers\EntityPreviewBuffer;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use GraphQL\Deferred;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Loads the entity associated with the current URL.
 */
#[DataProducer(
  id: "route_entity",
  name: new TranslatableMarkup("Load entity by uuid"),
  description: new TranslatableMarkup("The entity belonging to the current url."),
  produces: new ContextDefinition(
    data_type: "entity",
    label: new TranslatableMarkup("Entity"),
  ),
  consumes: [
    "url" => new ContextDefinition(
      data_type: "any",
      label: new TranslatableMarkup("The URL"),
    ),
    "language" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("Language"),
      required: FALSE,
    ),
  ],
)]
class RouteEntity extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

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
      $container->get('graphql.buffer.entity'),
      $container->get('graphql.buffer.entity_preview')
    );
  }

  /**
   * RouteEntity constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param \Drupal\Component\Plugin\Definition\PluginDefinitionInterface|array $pluginDefinition
   *   The plugin definition array.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The language manager service.
   * @param \Drupal\graphql\GraphQL\Buffers\EntityBuffer $entityBuffer
   *   The entity buffer service.
   * @param \Drupal\graphql\GraphQL\Buffers\EntityPreviewBuffer $entityPreviewBuffer
   *   The entity preview buffer service.
   */
  public function __construct(
    array $configuration,
    string $pluginId,
    PluginDefinitionInterface|array $pluginDefinition,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected EntityBuffer $entityBuffer,
    protected EntityPreviewBuffer $entityPreviewBuffer,
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * Resolver.
   *
   * @param \Drupal\Core\Url|string $url
   *   The URL to get the route entity from.
   * @param string|null $language
   *   The language code to get a translation of the entity.
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $context
   *   The GraphQL field context.
   */
  public function resolve(Url|string $url, ?string $language, FieldContext $context): ?Deferred {
    if (!$url instanceof Url) {
      return NULL;
    }

    [, $type] = explode('.', $url->getRouteName());
    $parameters = $url->getRouteParameters();

    // Previews.
    if (array_key_exists($type . '_preview', $parameters)) {
      return $this->resolvePreview($type, $parameters, $language, $context);
    }

    if (empty($parameters[$type])) {
      return NULL;
    }

    // Entities.
    return $this->resolveEntity($type, $parameters, $language, $context);
  }

  /**
   * Resolve an entity.
   *
   * @param string $type
   *   The entity type.
   * @param array $parameters
   *   The URL parameters.
   * @param string|null $language
   *   The language code to use.
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $context
   *   Cache context.
   *
   * @return \GraphQL\Deferred
   *   The deferred entity.
   */
  protected function resolveEntity(string $type, array $parameters, ?string $language, FieldContext $context): Deferred {
    $entity_id = (int) $parameters[$type];
    $resolver = $this->entityBuffer->add($type, $entity_id);

    return new Deferred(function () use ($type, $resolver, $context, $language) {
      if (!$entity = $resolver()) {
        // If there is no entity with this id, add the list cache tags so that
        // the cache entry is purged whenever a new entity of this type is
        // saved.
        return $this->resolveNotFound($type, $context);
      }

      // Get the correct translation.
      if (isset($language) && $language != $entity->language()->getId() && $entity instanceof TranslatableInterface) {
        $entity = $entity->getTranslation($language);
        $entity->addCacheContexts(["static:language:{$language}"]);
      }

      $access = $entity->access('view', NULL, TRUE);
      $context->addCacheableDependency($access);
      if ($access->isAllowed()) {
        return $entity;
      }
      return NULL;
    });
  }

  /**
   * Resolve a preview entity.
   *
   * @param string $type
   *   The entity type.
   * @param array $parameters
   *   The URL parameters.
   * @param string|null $language
   *   The language code to use.
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $context
   *   Cache context.
   *
   * @return \GraphQL\Deferred
   *   The deferred entity.
   */
  protected function resolvePreview(string $type, array $parameters, ?string $language, FieldContext $context): Deferred {
    $preview_id = $parameters[$type . '_preview'];
    $resolver = $this->entityPreviewBuffer->add($type, $preview_id);

    return new Deferred(function () use ($type, $resolver, $language, $context) {
      if (!$entity = $resolver()) {
        return $this->resolveNotFound($type, $context);
      }

      if (isset($language) && $language != $entity->language()->getId() && $entity instanceof TranslatableInterface) {
        $entity = $entity->getTranslation($language);
        $entity->addCacheContexts(["static:language:{$language}"]);
      }

      if (!$entity instanceof EntityInterface) {
        return $this->resolveNotFound($type, $context);
      }

      $access = $entity->access('view', NULL, TRUE);
      $context->addCacheableDependency($access);

      // Disable caching for accessible preview entities.
      if ($access->isAllowed()) {
        $context->setContextValue('preview', TRUE);
        $context->mergeCacheMaxAge(0);
        return $entity;
      }
      return NULL;
    });
  }

  /**
   * Resolve a not found entity.
   *
   * If there is no entity with this id, add the list cache tags so that
   * the cache entry is purged whenever a new entity of this type is
   * saved.
   *
   * @param string $type
   *   The entity type.
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $context
   *   Cache context.
   *
   * @return null
   *   Always null.
   *
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  private function resolveNotFound(string $type, FieldContext $context): mixed {
    $type_definition = $this->entityTypeManager->getDefinition($type, FALSE);
    if ($type_definition) {
      $context->addCacheTags($type_definition->getListCacheTags());
    }
    $context->addCacheTags(['4xx-response']);
    return NULL;
  }

}
