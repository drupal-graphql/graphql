<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Breadcrumbs;

use Drupal\Core\Breadcrumb\BreadcrumbManager;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\graphql\GraphQL\Batching\BatchedFieldResolver;
use Drupal\graphql\Plugin\GraphQL\Fields\SubrequestFieldBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the breadcrumbs.
 *
 * TODO: Move this to `InternalUrl` (breaking change).
 *
 * @GraphQLField(
 *   id = "breadcrumb",
 *   secure = true,
 *   name = "breadcrumb",
 *   type = "Link",
 *   multi = true,
 *   parents = {"Url"},
 * )
 */
class Breadcrumbs extends SubrequestFieldBase {

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
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * A http kernel to issue sub-requests to.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  protected $httpKernel;


  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('http_kernel'),
      $container->get('request_stack'),
      $container->get('graphql.batched_resolver'),
      $container->get('breadcrumb'),
      $container->get('current_route_match')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    HttpKernelInterface $httpKernel,
    RequestStack $requestStack,
    BatchedFieldResolver $batchedFieldResolver,
    BreadcrumbManager $breadcrumbManager,
    RouteMatchInterface $routeMatch
  ) {
    $this->breadcrumbManager = $breadcrumbManager;
    $this->routeMatch = $routeMatch;
    parent::__construct($configuration, $pluginId, $pluginDefinition, $httpKernel, $requestStack, $batchedFieldResolver);
  }

  /**
   * {@inheritdoc}
   */
  protected function resolveSubrequest($value, array $args, ResolveInfo $info) {
    return $this->breadcrumbManager->build($this->routeMatch)->getLinks();
  }

}
