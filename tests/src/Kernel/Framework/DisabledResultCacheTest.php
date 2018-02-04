<?php

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\graphql\GraphQL\Execution\QueryProcessor;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;

/**
 * Test disabled result cache.
 *
 * @group graphql
 */
class DisabledResultCacheTest extends GraphQLTestBase {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    parent::register($container);
    // Set the result_cache parameter to FALSE.
    $parameters = $container->getParameter('graphql.config');
    $parameters['result_cache'] = FALSE;
    $container->setParameter('graphql.config', $parameters);
  }

  /**
   * Test if disabling the result cache has the desired effect.
   */
  public function testDisabledCache() {
    $processor = $this->prophesize(QueryProcessor::class);
    $field = $this->mockField('root', [
      'id' => 'root',
      'name' => 'root',
      'type' => 'String',
    ]);

    $field
      ->expects(static::exactly(2))
      ->method('resolveValues')
      ->willReturnCallback(function () {
        yield 'test';
      });

    // The first request that is not supposed to be cached.
    $this->query('{ root }');

    // This should invoke the processor a second time.
    $this->query('{ root }');
  }

}
