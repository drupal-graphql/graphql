<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Field;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\Context\EntityContextDefinition;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\GraphQL\Buffers\EntityRevisionBuffer;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use GraphQL\Deferred;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Loads the entity reference revisions.
 */
#[DataProducer(
  id: "entity_reference_revisions",
  name: new TranslatableMarkup("Entity reference revisions"),
  description: new TranslatableMarkup("Loads entities from an entity reference revisions field."),
  produces: new ContextDefinition(
    data_type: "entity",
    label: new TranslatableMarkup("Entity"),
    multiple: TRUE,
  ),
  consumes: [
    "entity" => new ContextDefinition(
      data_type: "entity",
      label: new TranslatableMarkup("Parent entity"),
    ),
    "field" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("Field name"),
    ),
    "language" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("Language"),
      required: FALSE,
    ),
    "bundle" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("Entity bundle(s)"),
      multiple: TRUE,
      required: FALSE,
    ),
    "access" => new ContextDefinition(
      data_type: "boolean",
      label: new TranslatableMarkup("Check access"),
      required: FALSE,
      default_value: TRUE,
    ),
    "access_user" => new EntityContextDefinition(
      data_type: "entity:user",
      label: new TranslatableMarkup("User"),
      required: FALSE,
      default_value: NULL,
    ),
    "access_operation" => new ContextDefinition(
      data_type: "string",
      label: new TranslatableMarkup("Operation"),
      required: FALSE,
      default_value: "view",
    ),
  ],
)]
class EntityReferenceRevisions extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  use EntityReferenceTrait;

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
      $container->get('graphql.buffer.entity_revision')
    );
  }

  /**
   * EntityLoad constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param array $pluginDefinition
   *   The plugin definition array.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\graphql\GraphQL\Buffers\EntityRevisionBuffer $entityRevisionBuffer
   *   The entity revision buffer service.
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    array $configuration,
    string $pluginId,
    array $pluginDefinition,
    protected EntityTypeManagerInterface $entityTypeManager,
    protected EntityRevisionBuffer $entityRevisionBuffer,
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * Resolves entity reference revisions for a given field of a given entity.
   *
   * May optionally respect the entity bundles and language.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   The entity.
   * @param string $field
   *   The field of a given entity to get entity reference revisions for.
   * @param string|null $language
   *   Optional. Language to be respected for retrieved entities.
   * @param array|null $bundles
   *   Optional. List of bundles to be respected for retrieved entities.
   * @param bool $access
   *   Whether check for access or not. Default is true.
   * @param \Drupal\Core\Session\AccountInterface|null $accessUser
   *   User entity to check access for. Default is null.
   * @param string $accessOperation
   *   Operation to check access for. Default is view.
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $context
   *   The caching context related to the current field.
   *
   * @return \GraphQL\Deferred|array
   *   A promise that will return referenced entities or empty array if there
   *   aren't any.
   */
  public function resolve(EntityInterface $entity, string $field, ?string $language, ?array $bundles, bool $access, ?AccountInterface $accessUser, string $accessOperation, FieldContext $context): Deferred|array {
    if (!$entity instanceof FieldableEntityInterface || !$entity->hasField($field)) {
      return [];
    }

    $definition = $entity->getFieldDefinition($field);
    if ($definition->getType() !== 'entity_reference_revisions') {
      return [];
    }

    $definition = $entity->getFieldDefinition($field);
    $type = $definition->getSetting('target_type');
    $values = $entity->get($field);
    if ($values instanceof EntityReferenceFieldItemListInterface) {
      $resolvedEntities = [];
      $vids = [];

      foreach ($values as $delta => $item) {
        // If the entity is already present (common in preview),
        // use it directly and preserve ordering.
        if (isset($item->entity)) {
          $resolvedEntities[$delta] = $item->entity;
        }
        elseif (!empty($item->target_revision_id)) {
          $vids[$delta] = $item->target_revision_id;
        }
      }

      // If everything is already resolved, return immediately.
      if (empty($vids)) {
        if ($access) {
          $resolvedEntities = $this->filterAccessible($resolvedEntities, $accessUser, $accessOperation, $context);
        }
        return array_values($resolvedEntities);
      }

      $resolver = $this->entityRevisionBuffer->add($type, array_values($vids));

      return new Deferred(function () use ($type, $language, $bundles, $access, $accessUser, $accessOperation, $resolver, $context, $resolvedEntities, $vids) {
        $loadedEntities = $this->getReferencedEntities($type, $language, $bundles, $access, $accessUser, $accessOperation, $resolver, $context);

        // Merge resolved + loaded entities, preserving original order.
        foreach ($vids as $delta => $_vid) {
          if (isset($loadedEntities[$delta])) {
            $resolvedEntities[$delta] = $loadedEntities[$delta];
          }
        }

        ksort($resolvedEntities);
        return array_values($resolvedEntities);
      });
    }

    return [];
  }

}
