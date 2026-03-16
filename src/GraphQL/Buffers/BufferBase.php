<?php

declare(strict_types=1);

namespace Drupal\graphql\GraphQL\Buffers;

/**
 * Base class for field buffering services.
 */
abstract class BufferBase {

  /**
   * The the array of buffers.
   *
   * @var array<\SplObjectStorage<object, object>>
   */
  protected array $buffers = [];

  /**
   * The array of result sets.
   *
   * @var array<\SplObjectStorage<object, object>>
   */
  protected array $results = [];

  /**
   * Returns the bucket name for grouping items together.
   *
   * @param \ArrayObject<string, mixed> $item
   *   The item to get the buffer id for.
   *
   * @return string
   *   The buffer id.
   */
  protected function getBufferId(\ArrayObject $item): string {
    return "";
  }

  /**
   * Helper function to create a resolver for a singular buffer.
   *
   * @param \ArrayObject<string, mixed> $item
   *   The item to add to the buffer.
   *
   * @return \Closure
   *   The callback to invoke to load the result for this buffer item.
   */
  public function createBufferResolver(\ArrayObject $item): \Closure {
    $bufferId = $this->getBufferId($item);
    if (!isset($this->buffers[$bufferId])) {
      $this->buffers[$bufferId] = new \SplObjectStorage();
    }

    if (!isset($this->results[$bufferId])) {
      $this->results[$bufferId] = new \SplObjectStorage();
    }

    // Add the created item to the buffer.
    $this->buffers[$bufferId]->offsetSet($item, $item);

    // Return a callback that can be used to resolve the buffer item.
    return $this->createResolver($item, $this->buffers[$bufferId], $this->results[$bufferId]);
  }

  /**
   * Creates a callback to invoke to load the result for this buffer item.
   *
   * @param \ArrayObject<string, mixed> $item
   *   The item to add to create the resolver for.
   * @param \SplObjectStorage<object, object> $buffer
   *   The buffer.
   * @param \SplObjectStorage<object, object> $result
   *   The result set.
   *
   * @return \Closure
   *   The callback to invoke to load the result for this buffer item.
   */
  protected function createResolver(\ArrayObject $item, \SplObjectStorage $buffer, \SplObjectStorage $result): \Closure {
    // Return the closure that will resolve and return the result for the item.
    return function () use ($item, $buffer, $result) {
      return $this->resolveItem($item, $buffer, $result);
    };
  }

  /**
   * Returns the result of the given item after processing the buffer if needed.
   *
   * @param \ArrayObject<string, mixed> $item
   *   The buffer item to retrieve the result for.
   * @param \SplObjectStorage<object, object> $buffer
   *   The buffer.
   * @param \SplObjectStorage<object, object> $result
   *   The result set.
   *
   * @return mixed
   *   The result of resolving the given buffer item.
   */
  protected function resolveItem(\ArrayObject $item, \SplObjectStorage $buffer, \SplObjectStorage $result): mixed {
    if ($buffer->offsetExists($item)) {
      $results = $this->resolveBuffer($buffer);

      // Remove the resolved items from the buffer and add them to the results.
      $buffer->removeAll($results);
      $result->addAll($results);
    }

    if ($result->offsetExists($item)) {
      return $result[$item];
    }

    throw new \LogicException('Failed to resolve item.');
  }

  /**
   * Resolves the given buffer wholly.
   *
   * @param \SplObjectStorage<object, object> $buffer
   *   The buffer to be resolved wholly.
   *
   * @return \SplObjectStorage<object, object>
   *   The resolved results for the given buffer, keyed by the corresponding
   *   buffer items.
   */
  protected function resolveBuffer(\SplObjectStorage $buffer): \SplObjectStorage {
    // Convert the buffer to an array that we can later use to map the results
    // to the correct batch items.
    $buffer = iterator_to_array($buffer, FALSE);

    // Assign the loaded items to their corresponding batch items.
    $output = new \SplObjectStorage();
    foreach ($this->resolveBufferArray($buffer) as $key => $item) {
      $output->offsetSet($buffer[$key], $item);
    }

    return $output;
  }

  /**
   * Resolve the buffer as an array.
   *
   * Simplifies sub-class implementations by concealing the object storage
   * details of the buffer object.
   *
   * @param array $buffer
   *   The buffer as an array.
   *
   * @return array
   *   The resolved/loaded items.
   */
  abstract protected function resolveBufferArray(array $buffer): array;

}
