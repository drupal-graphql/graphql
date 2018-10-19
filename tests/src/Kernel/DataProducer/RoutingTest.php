<?php

namespace Drupal\Tests\graphql\Kernel\DataProducer;

use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Drupal\Core\Cache\CacheableMetadata;

/**
 * Data producers Routing test class.
 *
 * @group graphql
 */
class RoutingTest extends GraphQLTestBase {

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
    $this->dataProducerManager = $this->container->get('plugin.manager.graphql.data_producer');
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Routing\RouteLoad::resolve
   */
  public function testRouteLoad() {
    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'route_load',
      'configuration' => []
    ]);
    $metadata = new CacheableMetadata();
    $result = $plugin->resolve('/user/login', $metadata);
    $this->assertNotNull($result);
    $this->assertEquals('user.login', $result->getRouteName());
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Routing\Url\UrlPath::resolve
   */
  public function testUrlPath() {
    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'url_path',
      'configuration' => []
    ]);
    $metadata = new CacheableMetadata();
    $this->pathValidator = $this->container->get('path.validator');
    $url = $this->pathValidator->getUrlIfValidWithoutAccessCheck('/user/login');
    $this->assertEquals('/user/login', $plugin->resolve($url, $metadata));
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Routing\Url\UrlPath::resolve
   */
  public function testUrlNotFound() {
    $plugin = $this->dataProducerManager->getInstance([
      'id' => 'route_load',
      'configuration' => []
    ]);
    $metadata = new CacheableMetadata();
    $result = $plugin->resolve('/idontexist', $metadata);
    $this->assertContains('4xx-response', $metadata->getCacheTags());
    $this->assertNull($result);
  }

}
