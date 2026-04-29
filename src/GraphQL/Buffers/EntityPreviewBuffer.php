<?php

declare(strict_types=1);

namespace Drupal\graphql\GraphQL\Buffers;

use Drupal\Core\ParamConverter\ParamConverterInterface;
use Symfony\Component\Routing\Route;

/**
 * Entity preview buffer for GraphQL.
 */
class EntityPreviewBuffer extends BufferBase {

  /**
   * A list of parameter converters keyed by type.
   *
   * @var array<string, \Drupal\Core\ParamConverter\ParamConverterInterface>
   */
  protected array $typeConverters = [];

  /**
   * Constructs an EntityPreviewBuffer object.
   *
   * @param array<string, \Drupal\Core\ParamConverter\ParamConverterInterface> $converters
   *   Array of loaded converter services keyed by their ids.
   */
  public function __construct(
    protected array $converters = [],
  ) {}

  /**
   * Registers a parameter converter with the buffer.
   *
   * @param \Drupal\Core\ParamConverter\ParamConverterInterface $param_converter
   *   The added param converter instance.
   * @param string $id
   *   The parameter converter service id to register.
   *
   * @return $this
   */
  public function addConverter(ParamConverterInterface $param_converter, string $id) {
    $this->converters[$id] = $param_converter;
    return $this;
  }

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
      // For supported entity types only the value is used in the conversion,
      // and the name is used for debugging errors.
      $entities[$uuid] = $this->getConverter($type . '_preview')->convert($uuid, NULL, "graphql_entity_preview_buffer", []);
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

  /**
   * Lazy loads the converter service.
   *
   * @param string $type
   *   The type of the converter service to load.
   *
   * @return \Drupal\Core\ParamConverter\ParamConverterInterface
   *   The converter service.
   *
   * @throws \InvalidArgumentException
   *   In case the converter isn't registered.
   */
  protected function getConverter(string $type): ParamConverterInterface {
    if (isset($this->typeConverters[$type])) {
      return $this->typeConverters[$type];
    }

    $route = new Route("/");
    foreach ($this->converters as $converter) {
      if ($converter->applies(['type' => $type], "graphql_entity_preview_buffer", $route)) {
        $this->typeConverters[$type] = $converter;
        return $converter;
      }
    }

    throw new \InvalidArgumentException("Could not find converter for type '$type'.");
  }

}
