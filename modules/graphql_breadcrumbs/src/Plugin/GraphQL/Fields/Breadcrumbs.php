<?php

namespace Drupal\graphql_breadcrumbs\Plugin\GraphQL\Fields;

use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\AccessAwareRouter;
use Drupal\Core\Routing\RouteMatch;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Route;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Retrieve the breadcrumbs.
 *
 * @GraphQLField(
 *   id = "breadcrumb",
 *   name = "breadcrumb",
 *   type = "Link",
 *   multi = true,
 *   types = {"Url"},
 * )
 */
class Breadcrumbs extends FieldPluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

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
   * The router object.
   *
   * @var \Drupal\Core\Routing\AccessAwareRouter
   */
  protected $router;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('breadcrumb'),
      $container->get('current_route_match'),
      $container->get('router')
    );
  }

  /**
   * Constructs a new Breadcrumbs object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface $breadcrumbManager
   *   The breadcrumb manager service.
   * @param \Drupal\Core\Routing\RouteMatchInterface $routeMatch
   *   Current route match.
   *
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, BreadcrumbBuilderInterface $breadcrumbManager, RouteMatchInterface $routeMatch, AccessAwareRouter $router) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->breadcrumbManager = $breadcrumbManager;
    $this->routeMatch = $routeMatch;
    $this->router = $router;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    /** @var \Drupal\Core\Url $value */
    $info = \Drupal::service('router')->match($value->getInternalPath());
    $routeMatch = new RouteMatch($value->getRouteName(), new Route($value->getInternalPath(), $info), $info);
    $links = $this->breadcrumbManager->build($routeMatch)->getLinks();

    foreach ($links as $link) {
      yield $link;
    }
  }

}
