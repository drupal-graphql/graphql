<?php

namespace Drupal\graphql\Controller;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Youshido\GraphQL\Execution\Processor;
use Youshido\GraphQL\Field\AbstractField;
use Youshido\GraphQL\Parser\Ast\Query;

/**
 * Handles GraphQL requests.
 */
class RequestController implements ContainerInjectionInterface {

  /**
   * The processor service.
   *
   * @var \Youshido\GraphQL\Execution\Processor
   */
  protected $processor;

  /**
   * Constructs a RequestController object.
   *
   * @param \Youshido\GraphQL\Execution\Processor $processor
   */
  public function __construct(Processor $processor) {
    $this->processor = $processor;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('graphql.processor')
    );
  }

  /**
   * Handles GraphQL requests.
   *
   * @param \Symfony\Component\HttpFoundation\Request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON formatted response.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   */
  public function handleRequest(Request $request) {
    $body = (array) json_decode($request->getContent()) + [
      'query' => NULL,
      'variables' => NULL,
    ];

    $query = $request->query->has('query') ? $request->query->get('query') : $body['query'];
    $variables = $request->query->has('variables') ? $request->query->get('variables') : $body['variables'];

    if (empty($query)) {
      throw new NotFoundHttpException();
    }

    $variables = ($variables && is_string($variables) ? json_decode($variables) : $variables);
    $result = $this->processor->processPayload($query, (array) ($variables ?: []));

    $metadata = $this->getCacheableMetadata();

    $response = new CacheableJsonResponse($result->getResponseData());
    $response->addCacheableDependency($metadata);

    return $response->setPrivate();
  }

  /**
   * Determines the cacheable metadata.
   *
   * @return \Drupal\Core\Cache\CacheableMetadata
   *   The cacheable metadata.
   */
  protected function getCacheableMetadata() {
    $metadata = new CacheableMetadata();
    // @todo why we do this here.
    $queryType = $this->processor->getExecutionContext()
      ->getSchema()
      ->getQueryType();
    foreach ($queryType->getFields() as $field) {
      if ($field instanceof CacheableDependencyInterface) {
        $metadata->addCacheableDependency($field);
      }
    }

    return $metadata;
  }
}
