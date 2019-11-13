<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Routing;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\TranslatableInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\graphql\GraphQL\Buffers\EntityBuffer;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use GraphQL\Deferred;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * @DataProducer(
 *   id = "route_entity",
 *   name = @Translation("Load entity by uuid"),
 *   description = @Translation("The entity belonging to the current url."),
 *   produces = @ContextDefinition("entity",
 *     label = @Translation("Entity")
 *   ),
 *   consumes = {
 *     "url" = @ContextDefinition("any",
 *       label = @Translation("The URL")
 *     ),
 *     "language" = @ContextDefinition("string",
 *       label = @Translation("Language"),
 *       required = FALSE
 *     )
 *   }
 * )
 */
class RouteEntity extends DataProducerPluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;


  /**
   * The entity buffer service.
   *
   * @var \Drupal\graphql\GraphQL\Buffers\EntityBuffer
   */
  protected $entityBuffer;

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
      $container->get('graphql.buffer.entity')
    );
  }

  /**
   * RouteEntity constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition array.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The language manager service.
   * @param \Drupal\graphql\GraphQL\Buffers\EntityBuffer $entityBuffer
   *   The entity buffer service.
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    EntityTypeManagerInterface $entityTypeManager,
    EntityBuffer $entityBuffer
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->entityTypeManager = $entityTypeManager;
    $this->entityBuffer = $entityBuffer;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($url, $language = NULL, FieldContext $context) {
    if ($url instanceof Url) {
      list(, $type) = explode('.', $url->getRouteName());
      $parameters = $url->getRouteParameters();
      $id = $parameters[$type];
      $resolver = $this->entityBuffer->add($type, $id);

      return new Deferred(function () use ($type, $id, $resolver, $context, $language) {
        if (!$entity = $resolver()) {
          // If there is no entity with this id, add the list cache tags so that
          // the cache entry is purged whenever a new entity of this type is
          // saved.
          $type = $this->entityTypeManager->getDefinition($type);
          /** @var \Drupal\Core\Entity\EntityTypeInterface $type */
          $tags = $type->getListCacheTags();
          $context->addCacheTags($tags)->addCacheTags(['4xx-response']);
          return NULL;
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
  }
}
