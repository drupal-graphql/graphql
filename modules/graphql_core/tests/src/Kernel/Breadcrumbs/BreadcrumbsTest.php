<?php

namespace Drupal\Tests\graphql_core\Kernel\Breadcrumbs;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\Tests\graphql_core\Kernel\GraphQLCoreTestBase;
use Prophecy\Argument;

/**
 * Test entity query support in GraphQL.
 *
 * @group graphql_core
 */
class BreadcrumbsTest extends GraphQLCoreTestBase {

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'graphql_breadcrumbs_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $breadcrumbManager = $this->prophesize('Drupal\Core\Breadcrumb\BreadcrumbManager');
    $breadcrumbManager->build(Argument::any())
      ->will(function ($args) {
        /** @var \Drupal\Core\Routing\RouteMatch $routeMatch */
        $routeMatch = $args[0];
        $breadcrumb = new Breadcrumb();
        if ($routeMatch->getRouteName() == 'graphql_breadcrumbs_test.test') {
          $breadcrumb->addLink(new Link('Test breadcrumb', Url::fromUserInput('/breadcrumbs-test')));
        }

        return $breadcrumb;
      });

    $this->container->set('breadcrumb', $breadcrumbManager->reveal());
  }

  /**
   * Test that the breadcrumb query returns breadcrumbs for given path.
   */
  public function testBreadcrumbs() {
    $query = $this->getQueryFromFile('breadcrumbs.gql');
    $metadata = $this->defaultCacheMetaData();

    $this->assertResults($query, ['path' => '/breadcrumbs-test'], [
      'route' => [
        'breadcrumb' => [
          0 => ['text' => 'Test breadcrumb'],
        ],
      ],
    ], $metadata);
  }

}
