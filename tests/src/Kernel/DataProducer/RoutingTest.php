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
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Routing\RouteLoad::resolve
   */
  public function testRouteLoad() {
    $result = $this->executeDataProducer('route_load', [
      'path' => '/user/logout',
    ]);

    $this->assertNotNull($result);
    $this->assertEquals('user.logout', $result->getRouteName());
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Routing\Url\UrlPath::resolve
   */
  public function testUrlPath() {
    $this->pathValidator = $this->container->get('path.validator');
    $url = $this->pathValidator->getUrlIfValidWithoutAccessCheck('/user/logout');

    $result = $this->executeDataProducer('url_path', [
      'url' => $url,
    ]);

    $this->assertEquals('/user/logout', $result);
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Routing\Url\UrlPath::resolve
   */
  public function testUrlNotFound() {
    $result = $this->executeDataProducer('route_load', [
      'path' => '/idontexist',
    ]);

//    $this->assertContains('4xx-response', $metadata->getCacheTags());
    $this->assertNull($result);
  }

}
