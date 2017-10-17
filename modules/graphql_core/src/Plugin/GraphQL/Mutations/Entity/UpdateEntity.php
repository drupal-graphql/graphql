<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Mutations\Entity;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\graphql_core\GraphQL\EntityCrudOutputWrapper;
use Drupal\graphql\Plugin\GraphQL\Mutations\MutationPluginBase;
use Drupal\graphql_core\Plugin\GraphQL\Mutations\Entity\EntityMutationInputTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Update an entity.
 *
 * TODO: Add revision support.
 *
 * @GraphQLMutation(
 *   id = "update_entity",
 *   type = "EntityCrudOutput",
 *   secure = true,
 *   nullable = false,
 *   schema_cache_tags = {"entity_types", "entity_bundles"},
 *   deriver = "Drupal\graphql_core\Plugin\Deriver\Mutations\UpdateEntityDeriver"
 * )
 */
class UpdateEntity extends MutationPluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;
  use StringTranslationTrait;
  use EntityMutationInputTrait;

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
    $storage = $this->entityTypeManager->getStorage($entityTypeId);

    /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
    if (!$entity = $storage->load($args['id'])) {
      return new EntityCrudOutputWrapper(NULL, NULL, [
        $this->t('The requested @bundle could not be loaded.', ['@bundle' => $bundleName]),
      ]);
    }

    if (!$entity->bundle() === $bundleName) {
      return new EntityCrudOutputWrapper(NULL, NULL, [
        $this->t('The requested entity is not of the expected type @bundle.', ['@bundle' => $bundleName]),
      ]);
    }

    if (!$entity->access('update')) {
      return new EntityCrudOutputWrapper(NULL, NULL, [
        $this->t('You do not have the necessary permissions to update this @bundle.', ['@bundle' => $bundleName]),
      ]);
    }

    // The raw input needs to be converted to use the proper field and property
    // keys because we usually convert them to camel case when adding them to
    // the schema.
    $inputArgs = $args['input'];
    /** @var \Youshido\GraphQL\Type\Object\AbstractObjectType $type */
    $type = $this->config->getArgument('input')->getType();
    /** @var \Drupal\graphql_core\Plugin\GraphQL\InputTypes\Mutations\EntityInput $inputType */
    $inputType = $type->getNamedType();
    $input = $this->extractEntityInput($inputArgs, $inputType);

    try {
      foreach ($input as $key => $value) {
        $entity->get($key)->setValue($value);
      }
    }
    catch (\InvalidArgumentException $exception) {
      return new EntityCrudOutputWrapper(NULL, NULL, [
        $this->t('The entity update failed with exception: @exception.', ['@exception' => $exception->getMessage()]),
      ]);
    }

    if (($violations = $entity->validate()) && $violations->count()) {
      return new EntityCrudOutputWrapper(NULL, $violations);
    }

    if (($status = $entity->save()) && $status === SAVED_UPDATED) {
      return new EntityCrudOutputWrapper($entity);
    }

    return NULL;
  }


}
