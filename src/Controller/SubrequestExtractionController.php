<?php

namespace Drupal\graphql\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\graphql\Plugin\GraphQL\Fields\SubrequestFieldBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;

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
    $batch = $this
      ->requestStack->getCurrentRequest()
      ->attributes->get('graphql_subrequest');

    SubrequestFieldBase::processSubrequestBatch($batch);

    return Response::create();
  }

}
