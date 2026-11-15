<?php

declare(strict_types=1);

namespace Drupal\graphql\GraphQL\Buffers;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageException;
use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Collects entity UUIDs per entity type and loads them all at once in the end.
 *
 * @extends BufferBase<array<array-key, string>|string>
 */
class EntityUuidBuffer extends BufferBase {

  /**
   * EntityBuffer constructor.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   */
  public function __construct(
    protected EntityTypeManagerInterface $entityTypeManager,
  ) {
  }

  /**
   * Add an item to the buffer.
   *
   * @param string $type
   *   The entity type of the given entity ids.
   * @param array<array-key, string>|string $uuid
   *   The entity uuid(s) to load.
   *
   * @return \Closure
   *   The callback to invoke to load the result for this buffer item.
   */
  public function add(string $type, array|string $uuid): \Closure {
    $item = new \ArrayObject([
      'type' => $type,
      'uuid' => $uuid,
    ]);

    return $this->createBufferResolver($item);
  }

  /**
   * {@inheritdoc}
   */
  protected function getBufferId(\ArrayObject $item): string {
    /** @var string */
    return $item['type'];
  }

  /**
   * {@inheritdoc}
   */
  public function resolveBufferArray(array $buffer): array {
    /** @var array<array-key, \ArrayObject<string, array<array-key, string>|string>> $items */
    $items = $buffer;
    if ($items === []) {
      return [];
    }
    /** @var string $type */
    $type = reset($items)['type'];

    $entityType = $this->entityTypeManager->getDefinition($type);

    if (!$uuid_key = $entityType->getKey('uuid')) {
      throw new EntityStorageException("Entity type $type does not support UUIDs.");
    }

    $uuids = array_map(static function (\ArrayObject $item) {
      /** @var array<array-key, string>|string $ids */
      $ids = $item['uuid'];
      return (array) $ids;
    }, $items);

    $uuids = array_merge(...array_values($uuids));
    $uuids = array_values(array_unique($uuids));

    $entities = $this->entityTypeManager
      ->getStorage($type)->loadByProperties([$uuid_key => $uuids]);

    $entities = array_reduce(array_map(static function (EntityInterface $entity) {
      $uuid = $entity->uuid();
      if ($uuid === NULL) {
        throw new EntityStorageException('A loaded entity has no UUID.');
      }
      return [$uuid => $entity];
    }, $entities), 'array_merge', []);

    return array_map(static function ($item) use ($entities) {
      if (is_array($item['uuid'])) {
        return array_reduce($item['uuid'], static function ($carry, string $current) use ($entities) {
          if (!empty($entities[$current])) {
            return $carry + [$current => $entities[$current]];
          }

          return $carry;
        }, []);
      }

      return $entities[$item['uuid']] ?? NULL;
    }, $items);
  }

}
