<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Mutations\Entity;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityStorageException;
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

abstract class DeleteEntityBase extends MutationPluginBase implements ContainerFactoryPluginInterface {
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
   * DeleteEntityBase constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition array.
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
      $storage = $this->entityTypeManager->getStorage($entityTypeId);

      /** @var \Drupal\Core\Entity\ContentEntityInterface $entity */
      if (!$entity = $storage->load($args['id'])) {
        return new EntityCrudOutputWrapper(NULL, NULL, [
          $this->t('The requested entity could not be loaded.'),
        ]);
      }

      if (!$entity->access('delete')) {
        return new EntityCrudOutputWrapper(NULL, NULL, [
          $this->t('You do not have the necessary permissions to delete this entity.'),
        ]);
      }

      try {
        $entity->delete();
      }
      catch (EntityStorageException $exception) {
        return new EntityCrudOutputWrapper(NULL, NULL, [
          $this->t('Entity deletion failed with exception: @exception.', [
            '@exception' => $exception->getMessage(),
          ]),
        ]);
      }

      return new EntityCrudOutputWrapper($entity);
    });
  }

}
