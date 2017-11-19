<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Mutations\Entity;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\graphql\GraphQL\Type\InputObjectType;
use Drupal\graphql\Plugin\GraphQL\InputTypes\InputTypePluginBase;
use Drupal\graphql_core\GraphQL\EntityCrudOutputWrapper;
use Drupal\graphql\Plugin\GraphQL\Mutations\MutationPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

abstract class UpdateEntityBase extends MutationPluginBase implements ContainerFactoryPluginInterface {
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
    $type = $info->getField()->getArgument('input')->getType();
    /** @var \Drupal\graphql\GraphQL\Type\InputObjectType $inputType */
    $inputType = $type->getNamedType();
    $input = $this->extractEntityInput($inputArgs, $inputType, $info);

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

  /**
   * Extract entity values from the resolver args.
   *
   * Loops over all input values and assigns them to their original field names.
   *
   * @param array $inputArgs
   *   The entity values provided through the resolver args.
   * @param \Drupal\graphql\GraphQL\Type\InputObjectType $inputType
   *   The input type.
   * @param \Youshido\GraphQL\Execution\ResolveInfo $info
   *   The resolve info object.
   *
   * @return array
   *   The extracted entity values with their proper, internal field names.
   */
  abstract protected function extractEntityInput(array $inputArgs, InputObjectType $inputType, ResolveInfo $info);

}
