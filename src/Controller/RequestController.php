<?php

namespace Drupal\graphql\Controller;

use Drupal\Core\Cache\CacheableJsonResponse;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\graphql\GraphQL\Execution\QueryProcessor;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;

/**
 * Handles GraphQL requests.
 */
class RequestController implements ContainerInjectionInterface {

  /**
   * The query processor.
   *
   * @var \Drupal\graphql\GraphQL\Execution\QueryProcessor
   */
  protected $processor;

  /**
   * The service configuration parameters.
   *
   * @var array
   */
  protected $parameters;

  /**
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('graphql.query_processor'),
      $container->getParameter('graphql.config'),
      $container->get('renderer')
    );
  }

  /**
   * RequestController constructor.
   *
   * @param \Drupal\graphql\GraphQL\Execution\QueryProcessor $processor
   *   The query processor.
   * @param array $parameters
   *   The service configuration parameters.
   */
  public function __construct(QueryProcessor $processor, array $parameters, RendererInterface $renderer) {
    $this->processor = $processor;
    $this->parameters = $parameters;
    $this->renderer = $renderer;
  }

  /**
   * Handles graphql requests.
   *
   * @param string $schema
   *   The name of the schema.
   * @param \GraphQL\Server\OperationParams|\GraphQL\Server\OperationParams[] $operations
   *   The graphql operation(s) to execute.
   *
   * @return \Drupal\Core\Cache\CacheableJsonResponse
   *   The JSON formatted response.
   */
  public function handleRequest($schema, $operations) {
    $development = !empty($this->parameters['development']);
    $globals = ['development' => $development];

    if (is_array($operations)) {
      return $this->handleBatch($schema, $operations, $globals);
    }

    return $this->handleSingle($schema, $operations, $globals);
  }

  /**
   * @param $schema
   * @param $operations
   * @param array $globals
   *
   * @return \Drupal\Core\Cache\CacheableJsonResponse
   */
  protected function handleSingle($schema, $operations, $globals) {
    /** @var \Drupal\graphql\GraphQL\Execution\QueryResult $result */
    $result = [];
    $context = new RenderContext();
    $this->renderer->executeInRenderContext($context, function() use ($schema, $operations, $globals, &$result) {
      $result = $this->processor->processQuery($schema, $operations, $globals);
    });
    $response = new CacheableJsonResponse($result);
    $response->addCacheableDependency($result);
    return $response;
  }


  /**
   * @param $schema
   * @param $operations
   * @param array $globals
   *
   * @return \Drupal\Core\Cache\CacheableJsonResponse
   */
  protected function handleBatch($schema, $operations, $globals) {
    $result = $this->processor->processQuery($schema, $operations, $globals);
    $response = new CacheableJsonResponse($result);

    // In case of a batch request, the result is an array.
    $dependencies = is_array($operations) ? $result : [$result];
    foreach ($dependencies as $dependency) {
      $response->addCacheableDependency($dependency);
    }

    return $response;
  }

}
