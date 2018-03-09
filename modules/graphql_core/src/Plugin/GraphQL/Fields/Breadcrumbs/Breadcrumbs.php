<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Breadcrumbs;

use Drupal\Core\Breadcrumb\BreadcrumbManager;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Url;
use Drupal\graphql\GraphQL\Buffers\SubRequestBuffer;
use Drupal\graphql\GraphQL\Cache\CacheableValue;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Retrieve the breadcrumbs.
 *
 * @GraphQLField(
 *   id = "breadcrumb",
 *   secure = true,
 *   name = "breadcrumb",
 *   type = "[Link]",
 *   parents = {"InternalUrl"},
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
   * Breadcrumbs constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition.
   * @param \Drupal\graphql\GraphQL\Buffers\SubRequestBuffer $subRequestBuffer
   *   The sub-request buffer service.
   * @param \Drupal\Core\Breadcrumb\BreadcrumbManager $breadcrumbManager
   *   The breadcrumb manager service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   The current route match.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    SubRequestBuffer $subRequestBuffer,
    BreadcrumbManager $breadcrumbManager,
    RouteMatchInterface $routeMatch
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->subRequestBuffer = $subRequestBuffer;
    $this->breadcrumbManager = $breadcrumbManager;
    $this->routeMatch = $routeMatch;
  }

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof Url) {
      $resolve = $this->subRequestBuffer->add($value, function () {
        $links = $this->breadcrumbManager->build($this->routeMatch)->getLinks();
        return $links;
      });

      return function ($value, array $args, ResolveContext $context, ResolveInfo $info) use ($resolve) {
        /** @var \Drupal\graphql\GraphQL\Cache\CacheableValue $response */
        $response = $resolve();
        $links = $response->getValue();

        foreach ($links as $link) {
          yield new CacheableValue($link, [$response]);
        }
      };
    }
  }

}
