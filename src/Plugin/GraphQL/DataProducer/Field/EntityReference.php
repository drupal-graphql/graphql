<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Field;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManager;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Entity\TranslatableInterface;
use Drupal\Core\Field\EntityReferenceFieldItemListInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\GraphQL\Buffers\EntityBuffer;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use GraphQL\Deferred;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * @DataProducer(
 *   id = "entity_reference",
 *   name = @Translation("Entity reference"),
 *   description = @Translation("Loads entities from an entity reference field."),
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
 *       label = @Translation("Entity bundle(s)"),
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
class EntityReference extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * The entity repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

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
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   The entity repository service.
   * @param \Drupal\graphql\GraphQL\Buffers\EntityBuffer $entityBuffer
   *   The entity buffer service.
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    $configuration,
    $pluginId,
    $pluginDefinition,
    EntityTypeManager $entityTypeManager,
    EntityRepositoryInterface $entityRepository,
    EntityBuffer $entityBuffer
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->entityTypeManager = $entityTypeManager;
    $this->entityRepository = $entityRepository;
    $this->entityBuffer = $entityBuffer;
  }

  /**
   * @param \Drupal\Core\Entity\EntityInterface $entity
   * @param $field
   * @param null $language
   * @param null $bundles
   *
   * @param \Drupal\graphql\GraphQL\Execution\FieldContext $context
   *
   * @return \GraphQL\Deferred|null
   */
  public function resolve(EntityInterface $entity, $field, $language = NULL, $bundles = NULL, FieldContext $context) {
    if (!$entity instanceof FieldableEntityInterface || !$entity->hasField($field)) {
      return NULL;
    }

    $definition = $entity->getFieldDefinition($field);
    $type = $definition->getSetting('target_type');
    if (($values = $entity->get($field)) && $values instanceof EntityReferenceFieldItemListInterface) {
      $ids = array_map(function ($value) {
        return $value['target_id'];
      }, $values->getValue());

      $resolver = $this->entityBuffer->add($type, $ids);
      return new Deferred(function () use ($type, $language, $bundles, $resolver, $context) {
        $entities = $resolver() ?: [];
        $entities = isset($bundles) ? array_filter($entities, function (EntityInterface $entity) use ($bundles) {
          if (!in_array($entity->bundle(), $bundles)) {
            return FALSE;
          }

          return TRUE;
        }) : $entities;

        if (empty($entities)) {
          $type = $this->entityTypeManager->getDefinition($type);
          /** @var \Drupal\Core\Entity\EntityTypeInterface $type */
          $tags = $type->getListCacheTags();
          $context->addCacheTags($tags);
          return NULL;
        }

        if (isset($language)) {
          $entities = array_map(function (EntityInterface $entity) use ($language) {
            if ($language != $entity->language()->getId() && $entity instanceof TranslatableInterface) {
              $entity = $entity->getTranslation($language);
              $entity->addCacheContexts(["static:language:{$language}"]);
              return $entity;
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
