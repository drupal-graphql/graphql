<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Field;

use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Entity\TranslatableInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\graphql\GraphQL\Buffers\EntityRevisionBuffer;
use GraphQL\Deferred;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Loads the entity reference revisions.
 *
 * @DataProducer(
 *   id = "entity_reference_revisions",
 *   name = @Translation("Entity reference revisions"),
 *   description = @Translation("Loads entities from an entity reference revisions field."),
 *   produces = @ContextDefinition("entity",
 *     label = @Translation("Entity"),
 *     multiple = TRUE
 *   ),
 *   consumes = {
 *     "entity" = @ContextDefinition("entity",
 *       label = @Translation("Parent entity")
 *     ),
 *     "field" = @ContextDefinition("string",
 *       label = @Translation("Field name")
 *     ),
 *     "language" = @ContextDefinition("string",
 *       label = @Translation("Language"),
 *       multiple = TRUE,
 *       required = FALSE
 *     ),
 *     "bundle" = @ContextDefinition("string",
 *       label = @Translation("Entity bundle(s)"),
 *       multiple = TRUE,
 *       required = FALSE
 *     )
 *   }
 * )
 */
class EntityReferenceRevisions extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The entity revision buffer service.
   *
   * @var \Drupal\jobiqo_graphql\GraphQL\Buffers\EntityRevisionBuffer
   */
  protected $entityRevisionBuffer;

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
      $container->get('jobiqo_graphql.buffer.entity_revisions')
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
   * @param \Drupal\Core\Entity\EntityTypeManager $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\jobiqo_graphql\GraphQL\Buffers\EntityRevisionBuffer $entityRevisionBuffer
   *   The entity revision buffer service.
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    array $configuration,
    string $pluginId,
    array $pluginDefinition,
    EntityTypeManager $entityTypeManager,
    EntityRevisionBuffer $entityRevisionBuffer
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->entityTypeManager = $entityTypeManager;
    $this->entityRevisionBuffer = $entityRevisionBuffer;
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
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   *   The metadata object for caching.
   *
   * @return \GraphQL\Deferred|null
   *   A promise that will return entities or NULL if there aren't any.
   */
  public function resolve(EntityInterface $entity, string $field, ?string $language = NULL, ?array $bundles = NULL, RefinableCacheableDependencyInterface $metadata): ?Deferred {
    if (!$entity instanceof FieldableEntityInterface || !$entity->hasField($field)) {
      return NULL;
    }

    $definition = $entity->getFieldDefinition($field);
    if ($definition->getType() !== 'entity_reference_revisions') {
      return NULL;
    }

    $definition = $entity->getFieldDefinition($field);
    $type = $definition->getSetting('target_type');
    if (($values = $entity->get($field)) && $values instanceof EntityReferenceFieldItemListInterface) {
      $vids = array_map(function ($value) {
        return $value['target_revision_id'];
      }, $values->getValue());

      $resolver = $this->entityRevisionBuffer->add($type, $vids);
      return new Deferred(function () use ($type, $language, $bundles, $resolver, $metadata) {
        $entities = $resolver() ?: [];
        $entities = array_filter($entities, function (EntityInterface $entity) use ($bundles) {
          if (isset($bundles) && !in_array($entity->bundle(), $bundles)) {
            return FALSE;
          }

          // Filter out also entity where access is missing.
          $access = $entity->access('view', NULL, TRUE);
          if (!$access->isAllowed()) {
            return FALSE;
          }

          return TRUE;
        });

        if (empty($entities)) {
          $type = $this->entityTypeManager->getDefinition($type);
          /** @var \Drupal\Core\Entity\EntityTypeInterface $type */
          $tags = $type->getListCacheTags();
          $metadata->addCacheTags($tags);
          return NULL;
        }

        if (isset($language)) {
          $entities = array_map(function (EntityInterface $entity) use ($language) {
            if ($language != $entity->language()->getId() && $entity instanceof TranslatableInterface) {
              return $entity->getTranslation($language);
            }

            return $entity;
          }, $entities);
        }

        return $entities;
      });
    }

    return NULL;
  }

}
