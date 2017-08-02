<?php

namespace Drupal\graphql_core\GraphQL;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql_core\BatchedFieldResolver;
use Drupal\graphql_core\SubrequestResponse;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Base class for fields that have to pull information from subrequests.
 *
 * Requests to the same url are automatically batched.
 */
class SubrequestField extends FieldPluginBase implements ContainerFactoryPluginInterface, BatchedFieldInterface {
  use DependencySerializationTrait;

  /**
   * A http kernel to issue subrequests to.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  protected $httpKernel;

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The batched field resolver.
   *
   * @var \Drupal\graphql_core\BatchedFieldResolver
   */
  protected $batchedFieldResolver;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('http_kernel'),
      $container->get('request_stack'),
      $container->get('graphql_core.batched_resolver')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, HttpKernelInterface $httpKernel, RequestStack $requestStack, BatchedFieldResolver $batchedFieldResolver) {
    $this->httpKernel = $httpKernel;
    $this->requestStack = $requestStack;
    $this->batchedFieldResolver = $batchedFieldResolver;

    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * {@inheritdoc}
   */
  public function getBatchId($parent, array $arguments, ResolveInfo $info) {
    if (array_key_exists('path', $arguments)) {
      return $arguments['path'];
    }
    return parent::getBatchId($parent, $arguments, $info);
  }

  /**
   * {@inheritdoc}
   */
  public function getBatchedFieldResolver() {
    return $this->batchedFieldResolver;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveBatch(array $batch) {
    $first = reset($batch);

    // First, create an empty result set.
    $result = array_map(function () {
      return NULL;
    }, $batch);

    if ($first['arguments']['path']) {
      $currentRequest = $this->requestStack->getCurrentRequest();
      $request = Request::create(
        $first['arguments']['path'],
        'GET',
        $currentRequest->query->all(),
        $currentRequest->cookies->all(),
        $currentRequest->files->all(),
        $currentRequest->server->all()
      );

      $request->attributes->set(
        'graphql_subrequest',
        array_unique(array_map(function ($item) {
          return $item['arguments']['extract'];
        }, $batch))
      );

      if ($session = $currentRequest->getSession()) {
        $request->setSession($session);
      }

      $response = $this->httpKernel->handle($request);

      // TODO:
      // Remove the request stack manipulation once the core issue described at
      // https://www.drupal.org/node/2613044 is resolved.
      while ($this->requestStack->getCurrentRequest() === $request) {
        $this->requestStack->pop();
      }

      $result = [];
      if ($response instanceof SubrequestResponse) {
        foreach ($batch as $key => $item) {
          $result[$key] = $response->get($item['arguments']['extract']);
        }
      }

    }
    else {
      // TODO: get values from current request.
    }

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    yield $value;
  }

}
