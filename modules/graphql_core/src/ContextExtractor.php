<?php

namespace Drupal\graphql_core;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Service using HTTP kernel to extract Drupal context objects.
 *
 * Replaces the controller of requests containing the "graphql_context"
 * attribute with itself and returns a context response instead that will be
 * use as field value for graphql context fields.
 */
class ContextExtractor extends ControllerBase {

  /**
   * The context repository.
   *
   * @var \Drupal\Core\Plugin\Context\ContextRepositoryInterface
   */
  protected $contextRepository;

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
    return new static(
      $container->get('graphql_core.context_repository'),
      $container->get('request_stack')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(ContextRepositoryInterface $contextRepository, RequestStack $requestStack) {
    $this->contextRepository = $contextRepository;
    $this->requestStack = $requestStack;
  }

  /**
   * Extract the required context and return it.
   *
   * @return \Drupal\graphql_core\ContextResponse
   *   A context response instance.
   */
  public function extract() {
    $contextId = $this->requestStack->getCurrentRequest()->attributes->get('graphql_context');
    $response = new ContextResponse();
    $response->setContext($this->contextRepository->getRuntimeContexts([$contextId])[$contextId]);
    return $response;
  }

}
