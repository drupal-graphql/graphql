<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Entity;

use Drupal\Core\Access\AccessResultReasonInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Drupal\graphql\Plugin\GraphQL\DataProducer\EntityValidationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Creates an entity.
 *
 * @DataProducer(
 *   id = "create_entity",
 *   name = @Translation("Create Entity"),
 *   produces = @ContextDefinition("entity",
 *     label = @Translation("Entity")
 *   ),
 *   consumes = {
 *     "entity_type" = @ContextDefinition("string",
 *       label = @Translation("Entity Type"),
 *       required = TRUE
 *     ),
 *     "values" = @ContextDefinition("any",
 *       label = @Translation("Values to update"),
 *       required = TRUE
 *     ),
 *     "entity_return_key" = @ContextDefinition("string",
 *       label = @Translation("Entity Return Key"),
 *       required = TRUE
 *     ),
 *     "save" = @ContextDefinition("boolean",
 *       label = @Translation("Save entity"),
 *       required = FALSE,
 *       default_value = TRUE,
 *     ),
 *   }
 * )
 */
class CreateEntity extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  use EntityValidationTrait;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManager
   */
  protected $entityTypeManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static($configuration, $plugin_id, $plugin_definition);
    $instance->entityTypeManager = $container->get('entity_type.manager');
    return $instance;
  }

  /**
   * Resolve the values for this producer.
   */
  public function resolve(string $entity_type, array $values, string $entity_return_key, ?bool $save, $context) {
    $storage = $this->entityTypeManager->getStorage($entity_type);
    $accessHandler = $this->entityTypeManager->getAccessControlHandler($entity_type);

    // Ensure the user has access to create this kind of entity.
    $access = $accessHandler->createAccess(NULL, NULL, [], TRUE);
    $context->addCacheableDependency($access);
    if (!$access->isAllowed()) {
      return [
        'errors' => [$access instanceof AccessResultReasonInterface && $access->getReason() ? $access->getReason() : 'Access was forbidden.'],
      ];
    }

    $entity = $storage->create($values);
    if ($violation_messages = $this->getViolationMessages($entity)) {
      return [
        'errors' => $violation_messages,
      ];
    }

    if ($save) {
      $entity->save();
    }
    return [
      $entity_return_key => $entity,
    ];
  }

}
