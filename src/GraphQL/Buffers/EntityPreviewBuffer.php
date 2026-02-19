<?php

declare(strict_types=1);

namespace Drupal\graphql\GraphQL\Buffers;

use Drupal\Core\ParamConverter\ParamConverterInterface;

/**
 * Entity preview buffer for GraphQL.
 */
class EntityPreviewBuffer extends BufferBase {

  /**
   * Constructs a EntityPreviewBuffer object.
   *
   * @param \Drupal\Core\ParamConverter\ParamConverterInterface $convertor
   *   The entity preview converter. Currently thats just NodePreviewConverter.
   */
  public function __construct(
    protected ParamConverterInterface $convertor,
  ) {}

  /**
   * Add an item to the buffer.
   *
   * @param string $type
   *   The entity type of the given entity ids.
   * @param array|int|string $uuid
   *   The entity uuid(s) to load.
   *
   * @return \Closure
   *   The callback to invoke to load the result for this buffer item.
   */
  public function add(string $type, array|int|string $uuid): \Closure {
    $item = new \ArrayObject([
      'type' => $type,
      'uuid' => $uuid,
    ]);

    return $this->createBufferResolver($item);
  }

  /**
   * {@inheritdoc}
   */
  protected function getBufferId($item): string {
    return $item['type'] . '_preview';
  }

  /**
   * {@inheritdoc}
   */
  public function resolveBufferArray(array $buffer): array {
    $type = reset($buffer)['type'];
    $uuids = array_map(function (\ArrayObject $item) {
      return (array) $item['uuid'];
    }, $buffer);

    $uuids = call_user_func_array('array_merge', $uuids);
    $uuids = array_values(array_unique($uuids));

    $entities = [];
    foreach ($uuids as $uuid) {
      // Load the preview entity.
      $entities[$uuid] = $this->convertor->convert($uuid, NULL, $type . '_preview', []);
    }

    return array_map(function ($item) use ($entities) {
      if (is_array($item['uuid'])) {
        return array_reduce($item['uuid'], function ($carry, $current) use ($entities) {
          if (!empty($entities[$current])) {
            array_push($carry, $entities[$current]);
            return $carry;
          }

          return $carry;
        }, []);
      }

      return $entities[$item['uuid']] ?? NULL;
    }, $buffer);
  }

}
