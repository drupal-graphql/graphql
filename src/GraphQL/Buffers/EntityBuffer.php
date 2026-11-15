<?php

declare(strict_types=1);

namespace Drupal\graphql\GraphQL\Buffers;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Collects entity IDs per entity type and loads them all at once in the end.
 *
 * @extends BufferBase<array<array-key, int|string>|int|string>
 */
class EntityBuffer extends BufferBase {

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
   * @param array<array-key, int|string>|int|string $id
   *   The entity id(s) to load.
   *
   * @return \Closure
   *   The callback to invoke to load the result for this buffer item.
   */
  public function add(string $type, array|int|string $id): \Closure {
    $item = new \ArrayObject([
      'type' => $type,
      'id' => $id,
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
    if ($buffer === []) {
      return [];
    }

    /** @var \ArrayObject<string, array<array-key, int|string>|int|string> $first */
    $first = reset($buffer);
    $type = $this->getBufferId($first);
    $ids = array_map(static function (\ArrayObject $item) {
      /** @var array<array-key, int|string>|int|string $id */
      $id = $item['id'];
      return (array) $id;
    }, $buffer);

    $ids = array_merge(...array_values($ids));
    $ids = array_values(array_unique($ids));

    // Load the buffered entities.
    $entities = $this->entityTypeManager
      ->getStorage($type)
      ->loadMultiple($ids);

    return array_map(static function ($item) use ($entities) {
      if (is_array($item['id'])) {
        return array_reduce($item['id'], static function ($carry, $current) use ($entities) {
          if (!empty($entities[$current])) {
            array_push($carry, $entities[$current]);
            return $carry;
          }

          return $carry;
        }, []);
      }

      return $entities[$item['id']] ?? NULL;
    }, $buffer);
  }

}
