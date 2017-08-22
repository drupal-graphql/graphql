<?php

namespace Drupal\graphql_content_mutation\Plugin\GraphQL\Mutations;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\graphql_content_mutation\Plugin\GraphQL\DeleteEntityOutputWrapper;
use Drupal\graphql_core\GraphQL\MutationPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Delete an entity.
 *
 * @GraphQLMutation(
 *   id = "delete_entity",
 *   secure = true,
 *   name = "deleteEntity",
 *   type = "DeleteEntityOutput",
 *   arguments = {
 *     "id" = "String"
 *   },
 *   nullable = false,
 *   cache_tags = {"entity_types"},
 *   deriver = "\Drupal\graphql_content_mutation\Plugin\Deriver\DeleteEntityDeriver"
 * )
 */
class DeleteEntity extends MutationPluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;
  use StringTranslationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    $entityTypeId = $this->pluginDefinition['entity_type'];
    $storage = $this->entityTypeManager->getStorage($entityTypeId);

    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    if (!$entity = $storage->load($args['id'])) {
      return new DeleteEntityOutputWrapper(NULL, [
        $this->t('The requested entity could not be loaded.'),
      ]);
    }

    if (!$entity->access('delete')) {
      return new DeleteEntityOutputWrapper(NULL, [
        $this->t('You do not have the necessary permissions to delete this entity.'),
      ]);
    }

    try {
      $entity->delete();
    }
    catch (EntityStorageException $exception) {
      return new DeleteEntityOutputWrapper(NULL, [
        $this->t('Entity deletion failed with exception: @exception.', [
          '@exception' => $exception->getMessage(),
        ]),
      ]);
    }

    return new DeleteEntityOutputWrapper($entity);
  }

}
