<?php

/**
 * @file
 * Contains \Drupal\graphql\EventSubscriber\EntitySchemaSubscriber.
 */

namespace Drupal\graphql\EventSubscriber;

use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Entity\EntityTypeEvent;
use Drupal\Core\Entity\EntityTypeEventSubscriberTrait;
use Drupal\Core\Entity\EntityTypeListenerInterface;
use Drupal\Core\Field\FieldStorageDefinitionEvent;
use Drupal\Core\Field\FieldStorageDefinitionEventSubscriberTrait;
use Drupal\Core\Field\FieldStorageDefinitionListenerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Reacts to changes on entity types to clear the schema cache.
 */
class EntitySchemaSubscriber implements EntityTypeListenerInterface, FieldStorageDefinitionListenerInterface, EventSubscriberInterface {

  use FieldStorageDefinitionEventSubscriberTrait;
  use EntityTypeEventSubscriberTrait;

  /**
   * The schema cache backend.
   *
   * @var \Drupal\Core\Cache\CacheBackendInterface
   */
  protected $schemaCache;

  /**
   * Constructs a EntitySchemaSubscriber
   *
   * @param \Drupal\Core\Cache\CacheBackendInterface $schema_cache
   *   The schema cache backend.
   */
  public function __construct(CacheBackendInterface $schema_cache) {
    $this->schemaCache = $schema_cache;
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return array_merge(
      static::getEntityTypeEvents(),
      static::getFieldStorageDefinitionEvents()
    );
  }

  /**
   * {@inheritdoc}
   */
  public function onFieldStorageDefinitionEvent(FieldStorageDefinitionEvent $event, $event_name) {
    $this->schemaCache->deleteAll();
  }

  /**
   * {@inheritdoc}
   */
  public function onEntityTypeEvent(EntityTypeEvent $event, $event_name) {
    $this->schemaCache->deleteAll();
  }
}
