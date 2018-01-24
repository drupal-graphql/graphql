<?php

namespace Drupal\graphql\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\graphql\GraphQL\Buffers\SubRequestResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Extract arbitrary information from subrequests.
 */
class SubrequestExtractionController extends ControllerBase {

  /**
   * The symfony request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('request_stack'));
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(RequestStack $requestStack) {
    $this->requestStack = $requestStack;
  }

  /**
   * Process the subrequest batch.
   */
  public function extract() {
    $request = $this->requestStack->getCurrentRequest();
    $callback = $request->attributes->get('_graphql_subrequest');
    return new SubRequestResponse($callback());
  }

}
