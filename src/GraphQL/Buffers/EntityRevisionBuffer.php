<?php

declare(strict_types=1);

namespace Drupal\graphql\GraphQL\Buffers;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Entity revision buffer.
 *
 * @extends BufferBase<array<array-key, int|string>|int|string>
 */
class EntityRevisionBuffer extends BufferBase {

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
   * @param array<array-key, int|string>|int $vid
   *   The entity revision id(s) to load.
   *
   * @return \Closure
   *   The callback to invoke to load the result for this buffer item.
   */
  public function add(string $type, array|int $vid): \Closure {
    $item = new \ArrayObject([
      'type' => $type,
      'vid' => $vid,
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
    /** @var array<array-key, \ArrayObject<string, array<array-key, int|string>|int|string>> $items */
    $items = $buffer;
    if ($items === []) {
      return [];
    }
    /** @var string $type */
    $type = reset($items)['type'];
    $vids = array_map(static function (\ArrayObject $item) {
      /** @var array<array-key, int|string>|int|string $ids */
      $ids = $item['vid'];
      return (array) $ids;
    }, $items);

    $vids = array_merge(...array_values($vids));
    $vids = array_values(array_unique($vids));

    // Load the buffered entities.
    /** @var \Drupal\Core\Entity\RevisionableStorageInterface $storage */
    $storage = $this->entityTypeManager->getStorage($type);
    $entities = $storage->loadMultipleRevisions($vids);

    return array_map(static function ($item) use ($entities) {
      if (is_array($item['vid'])) {
        return array_reduce($item['vid'], static function ($carry, int|string $current) use ($entities) {
          if (!empty($entities[$current])) {
            return $carry + [$current => $entities[$current]];
          }

          return $carry;
        }, []);
      }

      return $entities[$item['vid']] ?? NULL;
    }, $items);
  }

}
