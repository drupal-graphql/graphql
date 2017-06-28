<?php

namespace Drupal\graphql_breadcrumbs;

use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Routing\RouteMatchInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Service using HTTP kernel to extract Drupal breadcrumbs.
 */
class BreadcrumbsExtractor extends ControllerBase {

  /**
   * The breadcrumb manager.
   *
   * @var \Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface
   */
  protected $breadcrumbManager;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('breadcrumb'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(BreadcrumbBuilderInterface $breadcrumbManager, RouteMatchInterface $routeMatch) {
    $this->breadcrumbManager = $breadcrumbManager;
    $this->routeMatch = $routeMatch;
  }

  /**
   * Extract the breadcrumbs and return them.
   *
   * @return \Drupal\graphql_breadcrumbs\BreadcrumbsResponse
   *   A metatag response instance.
   */
  public function extract() {
    $response = new BreadcrumbsResponse();
    $links = $this->breadcrumbManager->build($this->routeMatch)->getLinks();
    $response->setBreadcrumbs($links);
    return $response;
  }

}
