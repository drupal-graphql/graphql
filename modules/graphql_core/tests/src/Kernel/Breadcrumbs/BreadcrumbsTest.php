<?php

namespace Drupal\Tests\graphql_core\Kernel\Breadcrumbs;

use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Core\Url;
use Drupal\simpletest\ContentTypeCreationTrait;
use Drupal\simpletest\NodeCreationTrait;
use Drupal\Tests\graphql\Kernel\GraphQLFileTestBase;
use Prophecy\Argument;

/**
 * Test entity query support in GraphQL.
 *
 * @group graphql_breadcrumbs
 */
class BreadcrumbsTest extends GraphQLFileTestBase {
  use NodeCreationTrait;
  use ContentTypeCreationTrait;

  /**
   * {@inheritdoc}
   */
  public static $modules = [
    'graphql_core',
    'graphql_breadcrumbs_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $breadcrumbManager = $this->prophesize('Drupal\Core\Breadcrumb\BreadcrumbManager');

    $breadcrumbManager->build(Argument::any())
      ->will(function($args) {
        /** @var RouteMatch $routeMatch */
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
    $expected = [
      ['text' => 'Test breadcrumb']
    ];
    $result = $this->executeQueryFile('breadcrumbs.gql', ['path' => '/breadcrumbs-test']);
    $this->assertEquals($expected, $result['data']['route']['breadcrumb']);
  }

}
