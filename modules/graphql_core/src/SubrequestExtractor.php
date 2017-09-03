<?php

namespace Drupal\graphql_core;

use Drupal\Core\Controller\ControllerBase;
use Drupal\graphql_core\GraphQL\SubrequestField;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

/**
 * Extract arbitrary information from subrequests.
 */
class SubrequestExtractor extends ControllerBase {

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
    $batch = $this->requestStack->getCurrentRequest()
      ->attributes->get('graphql_subrequest');
    SubrequestField::processSubrequestBatch($batch);
    return Response::create();
  }

}
