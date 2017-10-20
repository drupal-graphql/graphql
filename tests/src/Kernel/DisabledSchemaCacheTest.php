<?php

namespace Drupal\Tests\graphql\Kernel;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\graphql\Traits\SchemaProphecyTrait;
use Prophecy\Argument;
use Youshido\GraphQL\Schema\Schema;
use Youshido\GraphQL\Type\Scalar\StringType;

/**
 * Test schema caching.
 *
 * @group graphql
 * @group cache
 */
class DisabledSchemaCacheTest extends KernelTestBase {
  use SchemaProphecyTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = ['graphql'];

  /**
   * {@inheritdoc}
   */
  public function register(ContainerBuilder $container) {
    $config = $container->getParameter('graphql.config');
    $config['schema_cache'] = FALSE;
    $container->setParameter('graphql.config', $config);
    $this->injectSchemaManager($container);
    parent::register($container);
  }

  /**
   * Test basic schema caching.
   */
  public function testDisabledCache() {
    // Prophesize a field with permanent cache.
    $metadata = new CacheableMetadata();
    $metadata->setCacheMaxAge(Cache::PERMANENT);
    $root = $this->prophesizeField('root', new StringType(), $metadata);
    $root->resolve(Argument::any())->willReturn('test');

    /** @var \Prophecy\Prophecy\MethodProphecy $getSchema */
    $schema = $this->createSchema($this->container, $root->reveal());
    $getSchema = $this->injectSchema($schema);

    $this->container->get('graphql.schema_loader')->getSchema('default');
    $getSchema->shouldHaveBeenCalledTimes(1);

    $this->container->get('graphql.schema_loader')->getSchema('default');
    $getSchema->shouldHaveBeenCalledTimes(2);
  }

}
