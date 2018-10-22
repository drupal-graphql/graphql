<?php

namespace Drupal\graphql_context_test\ContextProvider;

use Drupal\Component\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\Context\ContextProviderInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Context that exposes the current route name for testing purposes.
 */
class RouteNameContext implements ContextProviderInterface {
  use StringTranslationTrait;

  /**
   * The current route match.
   *
   * @var \Drupal\Core\Routing\RouteMatchInterface
   */
  protected $routeMatch;

  /**
   * The context definition.
   *
   * @var \Drupal\Core\Plugin\Context\ContextDefinition
   */
  protected $contextDefinition;

  /**
   * {@inheritdoc}
   */
  public function __construct(RouteMatchInterface $routeMatch) {
    $this->contextDefinition = new ContextDefinition('string', $this->t('The current path'));
    $this->routeMatch = $routeMatch;
  }

  /**
   * {@inheritdoc}
   */
  public function getRuntimeContexts(array $unqualifiedContextIds) {
    $name = $this->routeMatch->getRouteName();
    return ['route_name' => new Context($this->contextDefinition, $name)];
  }

  /**
   * {@inheritdoc}
   */
  public function getAvailableContexts() {
    $context = new Context($this->contextDefinition);
    return ['route_name' => $context];
  }

}
