<?php

namespace Drupal\graphql\Utility;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;

class UuidHelper {
  const TABLE = 'graphql_uuid';

  /**
   * The database service.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * UuidHelper constructor.
   *
   * @param \Drupal\Core\Database\Connection $connection
   *   The database service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   */
  public function __construct(Connection $connection, EntityTypeManagerInterface $entityTypeManager) {
    $this->connection = $connection;
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Load entities by UUIDs.
   *
   * @param array $uuids
   *   An array of UUIDs.
   *
   * @return array|null
   *   An entity-type-indexed hash of arrays of entity ids.
   */
  public function loadEntitiesByUuid(array $uuids) {
    $query = $this->connection
      ->select(static::TABLE, 'gu')
      ->fields('gu', ['type', 'id'])
      ->orderBy('type', 'id');
    $query->condition('uuid', $uuids, 'IN');
    $cursor = $query->execute();

    if (empty($cursor)) {
      return NULL;
    }

    $ids = [];
    foreach ($cursor as $row) {
      $type = $row->type;
      $ids[$type][] = $row->id;
    }

    $entities = [];
    foreach ($ids as $type => $type_ids) {
      $entities[$type] = $this->entityTypeManager
        ->getStorage($type)
        ->loadMultiple($type_ids);
    }

    return $entities;
  }

  /**
   * Load a single entity by UUID.
   *
   * @param string $uuid
   *
   * @return \Drupal\Core\Entity\EntityInterface|null
   */
  public function loadEntityByUuid($uuid) {
    $allEntities = $this->loadEntitiesByUuid([$uuid]);
    if (empty($allEntities)) {
      return NULL;
    }

    $typeEntities = reset($allEntities);
    $entity = reset($typeEntities);
    return $entity;
  }

}
