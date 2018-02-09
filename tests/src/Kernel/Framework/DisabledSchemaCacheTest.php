<?php

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;

/**
 * Test schema caching.
 *
 * @group graphql
 */
class DisabledSchemaCacheTest extends GraphQLTestBase {

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    parent::register($container);
    $config = $container->getParameter('graphql.config');
    $config['schema_cache'] = FALSE;
    $container->setParameter('graphql.config', $config);
  }

  /**
   * Test basic schema caching.
   */
  public function testDisabledCache() {
    $this->container->getDefinition('graphql.schema_loader')->setShared(FALSE);

    $this->mockField('root', [
      'id' => 'root',
      'name' => 'root',
      'type' => 'String',
    ], 'test');

    $this->schemaManager
      ->expects(static::exactly(2))
      ->method('createInstance')
      ->with(static::anything(), static::anything())
      ->willReturnCallback(function ($id) {
        return $this->mockSchema($id);
      });

    $this->container->get('graphql.schema_loader')->getSchema('default');
    $this->container->get('graphql.schema_loader')->getSchema('default');
  }

}
