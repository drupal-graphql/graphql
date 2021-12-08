<?php

namespace Drupal\Tests\graphql\Kernel\DataProducer;

use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Data producers Routing test class.
 *
 * @group graphql
 */
class RoutingTest extends GraphQLTestBase {

  /**
   * The redirect storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $redirectStorage;

  /**
   * {@inheritdoc}
   */
  protected static $modules =[
    'redirect',
    'path_alias',
    'views',
    ];

  /**
   * {@inheritdoc}
   */
  public function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('redirect');
    $this->installConfig(['redirect']);

    $this->redirectStorage = $this->container->get('entity_type.manager')->getStorage('redirect');
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Routing\RouteLoad::resolve
   */
  public function testRouteLoad(): void {
    $result = $this->executeDataProducer('route_load', [
      'path' => '/user/logout',
    ]);

    $this->assertNotNull($result);
    $this->assertEquals('user.logout', $result->getRouteName());
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Routing\RouteLoad::resolve
   */
  public function testRedirectRouteLoad(): void {
    /** @var \Drupal\redirect\Entity\Redirect $redirect */
    $redirect = $this->redirectStorage->create();
    $redirect->setSource('redirect-url');
    $redirect->setRedirect('user/logout');
    $redirect->save();

    $result = $this->executeDataProducer('route_load', [
      'path' => '/redirect-url',
    ]);

    $this->assertNotNull($result);
    $this->assertEquals('user.logout', $result->getRouteName());

    $redirect->setLanguage('de');
    $redirect->save();

    $result = $this->executeDataProducer('route_load', [
      'path' => '/redirect-url',
      'language' => 'de'
    ]);

    $this->assertNotNull($result);
    $this->assertEquals('user.logout', $result->getRouteName());
  }

  /**
   * @covers \Drupal\graphql\Plugin\GraphQL\DataProducer\Routing\Url\UrlPath::resolve
   */
  public function testUrlPath(): void {
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
  public function testUrlNotFound(): void {
    $result = $this->executeDataProducer('route_load', [
      'path' => '/idontexist',
    ]);

    // $this->assertContains('4xx-response', $metadata->getCacheTags());
    $this->assertNull($result);
  }

}
