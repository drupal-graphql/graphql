<?php

namespace Drupal\graphql\GraphQL\Buffers;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;

class EntityUuidBuffer extends BufferBase {

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * EntityBuffer constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Add an item to the buffer.
   *
   * @param string $type
   *   The entity type of the given entity ids.
   * @param array|string $uuid
   *   The entity uuid(s) to load.
   *
   * @return \Closure
   *   The callback to invoke to load the result for this buffer item.
   */
  public function add($type, $uuid) {
    $item = new \ArrayObject([
      'type' => $type,
      'uuid' => $uuid,
    ]);

    return $this->createBufferResolver($item);
  }

  /**
   * {@inheritdoc}
   */
  protected function getBufferId($item) {
    return $item['type'];
  }

  /**
   * {@inheritdoc}
   */
  public function resolveBufferArray(array $buffer) {
    $type = reset($buffer)['type'];

    $entityType = $this->entityTypeManager->getDefinition($type);

    if (!$uuid_key = $entityType->getKey('uuid')) {
      throw new EntityStorageException("Entity type $type does not support UUIDs.");
    }

    $uuids = array_map(function (\ArrayObject $item) {
      return (array) $item['uuid'];
    }, $buffer);

    $uuids = call_user_func_array('array_merge', $uuids);
    $uuids = array_values(array_unique($uuids));

    $entities = $this->entityTypeManager
      ->getStorage($type)->loadByProperties([$uuid_key => $uuids]);

    $entities = array_reduce(array_map(function (EntityInterface $entity) {
      return [$entity->uuid() => $entity];
    }, $entities), 'array_merge', []);

    return array_map(function ($item) use ($entities) {
      if (is_array($item['uuid'])) {
        return array_reduce($item['uuid'], function ($carry, $current) use ($entities) {
          if (!empty($entities[$current])) {
            return $carry + [$current => $entities[$current]];
          }

          return $carry;
        }, []);
      }

      return isset($entities[$item['uuid']]) ? $entities[$item['uuid']] : NULL;
    }, $buffer);
  }

}
