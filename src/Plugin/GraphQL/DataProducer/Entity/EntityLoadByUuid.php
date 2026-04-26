<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\TranslatableInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\Context\EntityContextDefinition;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\GraphQL\Buffers\EntityUuidBuffer;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use GraphQL\Deferred;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Loads an entity by UUID.
 */
#[DataProducer(
  id: "entity_load_by_uuid",
  name: new TranslatableMarkup("Load entity by uuid"),
  description: new TranslatableMarkup("Loads a single entity by uuid."),
  produces: new ContextDefinition(
    data_type: "entity",
    label: new TranslatableMarkup("Entity")
  ),
  consumes: [
    "type" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("Entity type")
    ),
    "uuid" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("Unique identifier")
    ),
    "language" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("Entity language"),
      required: FALSE
    ),
    "bundles" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("Entity bundle(s)"),
      multiple: TRUE,
      required: FALSE
    ),
    "access" => new ContextDefinition(
      data_type: "boolean",
      label: new TranslatableMarkup("Check access"),
      required: FALSE,
      default_value: TRUE
    ),
    "access_user" => new EntityContextDefinition(
      data_type: "entity:user",
      label: new TranslatableMarkup("User"),
      required: FALSE,
      default_value: NULL
    ),
    "access_operation" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("Operation"),
      required: FALSE,
      default_value: "view"
    ),
  ],
)]
class EntityLoadByUuid extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

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
      $container->get('entity_type.manager'),
      $container->get('entity.repository'),
      $container->get('graphql.buffer.entity_uuid')
    );
  }

  /**
   * Constructor.
   */
  public function __construct(
    array $configuration,
    string $pluginId,
    array $pluginDefinition,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected EntityRepositoryInterface $entityRepository,
    protected EntityUuidBuffer $entityBuffer,
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * Resolver.
   */
  public function resolve(string $type, string $uuid, ?string $language, ?array $bundles, ?bool $access, ?AccountInterface $accessUser, ?string $accessOperation, FieldContext $context): Deferred {
    $resolver = $this->entityBuffer->add($type, $uuid);

    return new Deferred(function () use ($type, $language, $bundles, $resolver, $context, $access, $accessUser, $accessOperation) {
      if (!$entity = $resolver()) {
        // If there is no entity with this id, add the list cache tags so that
        // the cache entry is purged whenever a new entity of this type is
        // saved.
        $type = $this->entityTypeManager->getDefinition($type);
        /** @var \Drupal\Core\Entity\EntityTypeInterface $type */
        $tags = $type->getListCacheTags();
        $context->addCacheTags($tags);
        return NULL;
      }

      $context->addCacheableDependency($entity);
      if (isset($bundles) && !in_array($entity->bundle(), $bundles)) {
        // If the entity is not among the allowed bundles, don't return it.
        return NULL;
      }

      // Get the correct translation.
      if (isset($language) && $language != $entity->language()->getId() && $entity instanceof TranslatableInterface) {
        $entity = $entity->getTranslation($language);
        $entity->addCacheContexts(["static:language:{$language}"]);
      }

      // Check if the passed user (or current user if none is passed) has access
      // to the entity, if not return NULL.
      if ($access) {
        /** @var \Drupal\Core\Access\AccessResultInterface $accessResult */
        $accessResult = $entity->access($accessOperation, $accessUser, TRUE);
        $context->addCacheableDependency($accessResult);
        if (!$accessResult->isAllowed()) {
          return NULL;
        }
      }

      return $entity;
    });
  }

}
