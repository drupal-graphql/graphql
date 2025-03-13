<?php

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
   * @param string $id
   * @param array $contexts
   *
   * @return mixed
   */
  protected function executeDataProducer($id, array $contexts = []) {
    /** @var \Drupal\graphql\Plugin\DataProducerPluginManager $manager */
    $manager = $this->container->get('plugin.manager.graphql.data_producer');

    /** @var \Drupal\graphql\Plugin\DataProducerPluginInterface $plugin */
    $plugin = $manager->createInstance($id);
    foreach ($contexts as $key => $value) {
      $plugin->setContextValue($key, $value);
    }

    $this->fieldContext = new TestFieldContext();

    $result = $plugin->resolveField($this->fieldContext);
    if (!$result instanceof SyncPromise) {
      return $result;
    }

    $adapter = new SyncPromiseAdapter();
    return $adapter->wait($adapter->convertThenable($result));
  }

}
