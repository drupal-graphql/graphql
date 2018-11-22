<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Mutations\Entity;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql_core\GraphQL\EntityCrudOutputWrapper;
use Drupal\graphql\Plugin\GraphQL\Mutations\MutationPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GraphQL\Type\Definition\ResolveInfo;

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
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('entity_type.manager'),
      $container->get('renderer')
    );
  }

  /**
   * UpdateEntityBase constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, EntityTypeManagerInterface $entityTypeManager, RendererInterface $renderer) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->entityTypeManager = $entityTypeManager;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveContext $context, ResolveInfo $info) {
    // There are cases where the Drupal entity API calls emit the cache metadata
    // in the current render context. In such cases
    // EarlyRenderingControllerWrapperSubscriber throws the leaked cache
    // metadata exception. To avoid this, wrap the execution in its own render
    // context.
    return $this->renderer->executeInRenderContext(new RenderContext(), function () use ($value, $args, $context, $info) {
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
      // the schema. Allow the other implementations to control this easily.
      $input = $this->extractEntityInput($value, $args, $context, $info);

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
    });
  }

  /**
   * Extract entity values from the resolver args.
   *
   * Loops over all input values and assigns them to their original field names.
   *
   * @param $value
   *   The parent value.
   * @param array $args
   *   The entity values provided through the resolver args.
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   *   The resolve context.
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *   The resolve info object.
   *
   * @return array
   *   The extracted entity values with their proper, internal field names.
   */
  abstract protected function extractEntityInput($value, array $args, ResolveContext $context, ResolveInfo $info);

}
