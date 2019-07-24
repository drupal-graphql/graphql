<?php

namespace Drupal\Tests\graphql\Traits;

use Drupal\graphql\GraphQL\Execution\FieldContext;
use GraphQL\Deferred;
use GraphQL\Executor\Promise\Adapter\SyncPromiseAdapter;
use Prophecy\Argument;

trait DataProducerExecutionTrait {

  /**
   * @param $id
   * @param array $contexts
   *
   * @return mixed
   */
  protected function executeDataProducer($id, $contexts = []) {
    /** @var \Drupal\graphql\Plugin\DataProducerPluginManager $manager */
    $manager = $this->container->get('plugin.manager.graphql.data_producer');

    /** @var \Drupal\graphql\Plugin\DataProducerPluginInterface $plugin */
    $plugin = $manager->createInstance($id);
    foreach ($contexts as $key => $value) {
      $plugin->setContextValue($key, $value);
    }

    $context = $this->prophesize(FieldContext::class);
    $context->addCacheableDependency(Argument::any())->willReturn($context->reveal());
    $context->addCacheContexts(Argument::any())->willReturn($context->reveal());
    $context->addCacheTags(Argument::any())->willReturn($context->reveal());
    $context->mergeCacheMaxAge(Argument::any())->willReturn($context->reveal());
    $context->getContextValue(Argument::any(), Argument::any())->willReturn(NULL);
    $context->setContextValue(Argument::any(), Argument::any())->willReturn(FALSE);
    $context->hasContextValue(Argument::any())->willReturn(FALSE);

    $result = $plugin->resolveField($context->reveal());
    if (!$result instanceof Deferred) {
      return $result;
    }

    $adapter = new SyncPromiseAdapter();
    return $adapter->wait($adapter->convertThenable($result));
  }

}
