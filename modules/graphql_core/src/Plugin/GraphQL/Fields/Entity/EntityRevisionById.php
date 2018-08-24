<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Entity;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\TypedData\TranslatableInterface;
use Drupal\graphql\GraphQL\Cache\CacheableValue;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * @GraphQLField(
 *   id = "entity_revision_by_id",
 *   secure = true,
 *   arguments = {
 *     "id" = "String!"
 *   },
 *   contextual_arguments = {"language"},
 *   deriver = "Drupal\graphql_core\Plugin\Deriver\Fields\EntityRevisionByIdDeriver"
 * )
 */
class EntityRevisionById extends FieldPluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('entity_type.manager'),
      $container->get('entity.repository')
    );
  }

  /**
   * EntityRevisionById constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entityRepository
   *   The entity repository service.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    EntityTypeManagerInterface $entityTypeManager,
    EntityRepositoryInterface $entityRepository
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->entityTypeManager = $entityTypeManager;
    $this->entityRepository = $entityRepository;
  }

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    $definition = $this->getPluginDefinition();
    $storage = $this->entityTypeManager->getStorage($definition['entity_type']);

    if (!$entity = $storage->loadRevision($args['id'])) {
      // If there is no entity with this id, add the list cache tags so that the
      // cache entry is purged whenever a new entity of this type is saved.
      $pluginDefinition = $this->getPluginDefinition();
      $entityType = $this->entityTypeManager->getDefinition($pluginDefinition['entity_type']);
      $metadata = new CacheableMetadata();
      $metadata->addCacheTags($entityType->getListCacheTags());

      yield new CacheableValue(NULL, [$metadata]);
    }
    /** @var \Drupal\Core\Access\AccessResultInterface $access */
    else if (($access = $entity->access('view', NULL, TRUE)) && $access->isAllowed()) {
      if ($entity instanceof TranslatableInterface && isset($args['language']) && $args['language'] != $entity->language()->getId() && $entity->isTranslatable()) {
        if ($entity->hasTranslation($args['language'])) {
          $entity = $entity->getTranslation($args['language']);
        }
      }

      yield new CacheableValue($entity, [$access]);
    }
    else {
      // If the entity exists but we do not grant access to it, we still want
      // to have it's cache metadata in the output because future changes to
      // the entity might affect its visibility for the user.
      yield new CacheableValue(NULL, [$access]);
    }
  }

}
