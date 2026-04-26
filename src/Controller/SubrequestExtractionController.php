<?php

declare(strict_types=1);

namespace Drupal\graphql\Controller;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\RendererInterface;
use Drupal\graphql\SubRequestResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Extract arbitrary information from subrequests.
 */
class SubrequestExtractionController extends ControllerBase {

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
   */
  public static function create(ContainerInterface $container): self {
    return new static(
      $container->get('request_stack'),
      $container->get('renderer')
    );
  }

  /**
   * SubrequestExtractionController constructor.
   *
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   *
   * @codeCoverageIgnore
   */
  public function __construct(
    protected RequestStack $requestStack,
    protected RendererInterface $renderer,
  ) {
  }

  /**
   * Extracts the sub-request callback response.
   *
   * @return \Drupal\graphql\SubRequestResponse
   *   The sub-request response object.
   */
  public function extract(): SubRequestResponse {
    $request = $this->requestStack->getCurrentRequest();
    $callback = $request->attributes->get('_graphql_subrequest');

    $metadata = new CacheableMetadata();
    $response = new SubRequestResponse($callback($metadata));
    $response->addCacheableDependency($metadata);

    return $response;
  }

}
