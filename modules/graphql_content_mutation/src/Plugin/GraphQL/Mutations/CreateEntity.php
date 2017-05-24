<?php

namespace Drupal\graphql_content_mutation\Plugin\GraphQL\Mutations;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\graphql_core\GraphQL\MutationPluginBase;
use Drupal\graphql_content_mutation\Plugin\GraphQL\CreateEntityOutputWrapper;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Create an entity.
 *
 * @GraphQLMutation(
 *   id = "create_entity",
 *   name = "createEntity",
 *   type = "CreateEntityOutput",
 *   nullable = false,
 *   cache_tags = {"entity_types", "entity_bundles"},
 *   deriver = "\Drupal\graphql_content_mutation\Plugin\Deriver\CreateEntityDeriver"
 * )
 */
class CreateEntity extends MutationPluginBase implements ContainerFactoryPluginInterface {
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
    $bundleName = $this->pluginDefinition['entity_bundle'];
    $bundleKey = $this->entityTypeManager->getDefinition($entityTypeId)->getKey('bundle');
    $storage = $this->entityTypeManager->getStorage($entityTypeId);

    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    $entity = $storage->create($args['input'] + [
      $bundleKey => $bundleName,
    ]);

    if (!$entity->access('create')) {
      return new CreateEntityOutputWrapper(NULL, NULL, [
        $this->t('You do not have the necessary permissions to create entities of this type.'),
      ]);
    }

    if (($violations = $entity->validate()) && $violations->count()) {
      return new CreateEntityOutputWrapper(NULL, $violations);
    }

    if (($status = $entity->save()) && $status === SAVED_NEW) {
      return new CreateEntityOutputWrapper($entity);
    }

    return NULL;
  }

}
