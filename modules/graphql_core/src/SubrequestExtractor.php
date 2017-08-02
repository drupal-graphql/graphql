<?php

namespace Drupal\graphql_core;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

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
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(RequestStack $requestStack, ModuleHandlerInterface $moduleHandler) {
    $this->requestStack = $requestStack;
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * Extract the required context and return it.
   *
   * @return \Drupal\graphql_core\SubrequestResponse
   *   A subrequest response instance.
   */
  public function extract() {
    $requirements = $this->requestStack->getCurrentRequest()->attributes->get('graphql_subrequest');
    $data = [];
    $this->moduleHandler->alter('graphql_subrequest', $data, $requirements);
    return new SubrequestResponse($data);
  }

}
