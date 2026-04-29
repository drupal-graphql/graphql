<?php

declare(strict_types=1);

namespace Drupal\Tests\graphql\Traits;

use Drupal\Tests\graphql\Kernel\TestFieldContext;
use GraphQL\Executor\Promise\Adapter\SyncPromise;
use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;

/**
 * Helper trait for testing data producers.
 */
trait DataProducerExecutionTrait {

  /**
   * A mock of the field context that can be used to check cache metadata.
   */
  protected TestFieldContext $fieldContext;

  /**
   * Executes the given data producer by ID.
   *
   * @param string $id
   *   The data producer plugin ID.
   * @param array<string, mixed> $contexts
   *   The context values to pass to the data producer.
   * @param string|null $language
   *   The language that should be set on the field context during execution, if
   *   any.
   *
   * @return mixed
   *   The result of the data producer execution.
   */
  protected function executeDataProducer(string $id, array $contexts = [], ?string $language = NULL): mixed {
    /** @var \Drupal\graphql\Plugin\DataProducerPluginManager $manager */
    $manager = $this->container->get('plugin.manager.graphql.data_producer');

    /** @var \Drupal\graphql\Plugin\DataProducerPluginInterface $plugin */
    $plugin = $manager->createInstance($id);
    foreach ($contexts as $key => $value) {
      $plugin->setContextValue($key, $value);
    }

    $this->fieldContext = new TestFieldContext($language);

    $result = $plugin->resolveField($this->fieldContext);
    if (!$result instanceof SyncPromise) {
      return $result;
    }

    $adapter = new SyncPromiseAdapter();
    return $adapter->wait($adapter->convertThenable($result));
  }

}
