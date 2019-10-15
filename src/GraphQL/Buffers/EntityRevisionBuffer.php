<?php

namespace Drupal\graphql\GraphQL\Buffers;

use Drupal\Core\Entity\EntityTypeManagerInterface;

/**
 * Entity revision buffer.
 */
class EntityRevisionBuffer extends BufferBase {

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
   * @param array|int $vid
   *   The entity revision id(s) to load.
   *
   * @return \Closure
   *   The callback to invoke to load the result for this buffer item.
   */
  public function add(string $type, $vid): \Closure {
    $item = new \ArrayObject([
      'type' => $type,
      'vid' => $vid,
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
    $vids = array_map(function (\ArrayObject $item) {
      return (array) $item['vid'];
    }, $buffer);

    $vids = call_user_func_array('array_merge', $vids);
    $vids = array_values(array_unique($vids));

    // Load the buffered entities.
    $entities = $this->entityTypeManager
      ->getStorage($type)
      ->loadMultipleRevisions($vids);

    return array_map(function ($item) use ($entities) {
      if (is_array($item['vid'])) {
        return array_reduce($item['vid'], function ($carry, $current) use ($entities) {
          if (!empty($entities[$current])) {
            return $carry + [$current => $entities[$current]];
          }

          return $carry;
        }, []);
      }

      return isset($entities[$item['vid']]) ? $entities[$item['vid']] : NULL;
    }, $buffer);
  }

}
