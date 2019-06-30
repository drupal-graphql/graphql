<?php

namespace Drupal\graphql\Controller;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\graphql\SubRequestResponse;
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
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('request_stack'),
      $container->get('language_manager'),
      $container->get('renderer')
    );
  }

  /**
   * SubrequestExtractionController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   *
   * @codeCoverageIgnore
   */
  public function __construct(RequestStack $requestStack, LanguageManagerInterface $languageManager, RendererInterface $renderer) {
    $this->requestStack = $requestStack;
    $this->languageManager = $languageManager;
    $this->renderer = $renderer;
  }

  /**
   * Extracts the sub-request callback response.
   *
   * @return \Drupal\graphql\SubRequestResponse
   *   The sub-request response object.
   */
  public function extract() {
    $request = $this->requestStack->getCurrentRequest();
    $callback = $request->attributes->get('_graphql_subrequest');

    $metadata = new CacheableMetadata();
    $response = new SubRequestResponse($callback($metadata));
    $response->addCacheableDependency($metadata);

    return $response;
  }

}
