<?php

namespace Drupal\graphql\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Language\LanguageManagerInterface;
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
    return new static(
      $container->get('request_stack'),
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(RequestStack $requestStack, LanguageManagerInterface $languageManager) {
    $this->requestStack = $requestStack;
    $this->languageManager = $languageManager;
  }

  /**
   * Extracts the sub-request callback response.
   *
   * @return \Drupal\graphql\GraphQL\Buffers\SubRequestResponse
   *   The sub-request response object.
   */
  public function extract() {
    $request = $this->requestStack->getCurrentRequest();
    $callback = $request->attributes->get('_graphql_subrequest');

    // TODO: Remove this once https://www.drupal.org/project/drupal/issues/2940036#comment-12479912 is resolved.
    $this->languageManager->reset();

    return new SubRequestResponse($callback());
  }

}
