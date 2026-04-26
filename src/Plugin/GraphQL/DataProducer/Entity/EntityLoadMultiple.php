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
use Drupal\graphql\GraphQL\Buffers\EntityBuffer;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use GraphQL\Deferred;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Load multiple entities by IDs.
 */
#[DataProducer(
  id: "entity_load_multiple",
  name: new TranslatableMarkup("Load multiple entities"),
  description: new TranslatableMarkup("Loads multiple entities."),
  produces: new ContextDefinition(
    data_type: "entity",
    label: new TranslatableMarkup("Entities"),
    multiple: TRUE
  ),
  consumes: [
    "type" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("Entity type")
    ),
    "ids" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("Identifier"),
      multiple: TRUE
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
class EntityLoadMultiple extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

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
      $container->get('graphql.buffer.entity')
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
    protected EntityBuffer $entityBuffer,
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * Resolver.
   */
  public function resolve(string $type, array $ids, ?string $language, ?array $bundles, bool $access, ?AccountInterface $accessUser, ?string $accessOperation, FieldContext $context): Deferred {
    // Remove any NULL IDs.
    $ids = array_filter($ids);

    $resolver = $this->entityBuffer->add($type, $ids);

    return new Deferred(function () use ($type, $language, $bundles, $resolver, $context, $access, $accessUser, $accessOperation) {
      /** @var array<\Drupal\Core\Entity\EntityInterface> $entities */
      $entities = $resolver();
      if (!$entities) {
        // If there is no entity with this id, add the list cache tags so that
        // the cache entry is purged whenever a new entity of this type is
        // saved.
        $type = $this->entityTypeManager->getDefinition($type);
        /** @var \Drupal\Core\Entity\EntityTypeInterface $type */
        $tags = $type->getListCacheTags();
        $context->addCacheTags($tags);
        return [];
      }

      foreach ($entities as $id => $entity) {
        $context->addCacheableDependency($entities[$id]);
        if (isset($bundles) && !in_array($entities[$id]->bundle(), $bundles)) {
          // If the entity is not among the allowed bundles, don't return it.
          unset($entities[$id]);
          continue;
        }

        if (isset($language) && $language !== $entities[$id]->language()->getId() && $entities[$id] instanceof TranslatableInterface) {
          $entities[$id] = $entities[$id]->getTranslation($language);
          $entities[$id]->addCacheContexts(["static:language:{$language}"]);
        }

        if ($access) {
          /** @var \Drupal\Core\Access\AccessResultInterface $accessResult */
          $accessResult = $entities[$id]->access($accessOperation, $accessUser, TRUE);
          $context->addCacheableDependency($accessResult);
          // We need to call isAllowed() because isForbidden() returns FALSE
          // for neutral access results, which is dangerous. Only an explicitly
          // allowed result means that the user has access.
          if (!$accessResult->isAllowed()) {
            unset($entities[$id]);
            continue;
          }
        }
      }

      return $entities;
    });
  }

}
