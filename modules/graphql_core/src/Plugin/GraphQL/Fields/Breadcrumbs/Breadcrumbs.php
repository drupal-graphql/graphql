<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Breadcrumbs;

use Drupal\Core\Breadcrumb\BreadcrumbManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\graphql\GraphQL\Buffers\SubRequestBuffer;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
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
 *   type = "[Link]",
 *   parents = {"Url"},
 * )
 */
class Breadcrumbs extends FieldPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The subrequest buffer service.
   *
   * @var \Drupal\graphql\GraphQL\Buffers\SubRequestBuffer
   */
  protected $subRequestBuffer;

  /**
   * The breadcrumb manager service.
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
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('graphql.buffer.subrequest'),
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
    SubRequestBuffer $subRequestBuffer,
    BreadcrumbManager $breadcrumbManager,
    RouteMatchInterface $routeMatch
  ) {
    $this->subRequestBuffer = $subRequestBuffer;
    $this->breadcrumbManager = $breadcrumbManager;
    $this->routeMatch = $routeMatch;
    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof Url) {
      $resolve = $this->subRequestBuffer->add($value, function () {
        $links = $this->breadcrumbManager->build($this->routeMatch)->getLinks();
        return $links;
      });

      return function ($value, array $args, ResolveInfo $info) use ($resolve) {
        $links = $resolve();
        foreach ($links as $link) {
          yield $link;
        }
      };
    }
  }

}
