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
    // Set the development parameter to TRUE.
    $parameters = $container->getParameter('graphql.config');
    $parameters['development'] = TRUE;
    $container->setParameter('graphql.config', $parameters);
  }

  /**
   * Test if disabling the result cache has the desired effect.
   */
  public function testDisabledCache() {
    $this->mockField('root', [
      'id' => 'root',
      'name' => 'root',
      'type' => 'String',
    ], NULL, function ($field) {
      $field
        ->expects(static::exactly(2))
        ->method('resolveValues')
        ->willReturnCallback(function () {
          yield 'test';
        });
    });

    // The first request that is not supposed to be cached.
    $this->query('{ root }');

    // This should invoke the processor a second time.
    $this->query('{ root }');
  }

}
