<?php

namespace Drupal\graphql\Plugin\GraphQL\Fields;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\graphql\GraphQL\Batching\BatchedFieldInterface;
use Drupal\graphql\GraphQL\Batching\BatchedFieldResolver;
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
abstract class SubrequestFieldBase extends FieldPluginBase implements ContainerFactoryPluginInterface, BatchedFieldInterface {
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
   * @var \Drupal\graphql\GraphQL\Batching\BatchedFieldResolver
   */
  protected $batchedFieldResolver;

  /**
   * The result of this specific subrequest.
   *
   * @var mixed
   */
  protected $subrequestResult;

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
      $container->get('graphql.batched_resolver')
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
    if ($parent instanceof Url) {
      return $parent->toString();
    }

    return parent::getBatchId($parent, $arguments, $info);
  }

  /**
   * {@inheritdoc}
   */
  public function getBatchedFieldResolver($value, array $args, ResolveInfo $info) {
    return $this->batchedFieldResolver;
  }

  /**
   * @param $value
   *   The GraphQL parent value.
   * @param array $args
   *   The field arguments.
   * @param \Youshido\GraphQL\Execution\ResolveInfo $info
   *   GraphQL resolve info
   */
  public function doResolveSubrequest($value, array $args, ResolveInfo $info) {
    $this->subrequestResult = $this->resolveSubrequest($value, $args, $info);
  }

  /**
   * Retrieve the subrequest result.
   *
   * @return mixed
   */
  public function getSubrequestResult() {
    return $this->subrequestResult;
  }

  /**
   * Resolve the subrequest value.
   *
   * Will be executed within a subrequest context.
   *
   * @param $value
   *   The parent GraphQL result value.
   * @param array $args
   *   The field arguments.
   * @param \Youshido\GraphQL\Execution\ResolveInfo $info
   *   GraphQL resolve info
   *
   * @return mixed
   *   The result value.
   */
  abstract protected function resolveSubrequest($value, array $args, ResolveInfo $info);

  /**
   * Evaluate all batched SubrequestFields.
   *
   * Called either from within the current request or a subrequest context.
   *
   * @param mixed $batch
   *   The batch queue.
   */
  final public static function processSubrequestBatch($batch) {
    foreach ($batch as $item) {
      if ($item['field'] instanceof SubrequestFieldBase) {
        $item['field']->doResolveSubrequest($item['parent'], $item['arguments'], $item['info']);
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function resolveBatch(array $batch) {
    $first = reset($batch);
    $url = $first['parent'];

    if ($url instanceof Url) {
      $currentRequest = $this->requestStack->getCurrentRequest();
      $request = Request::create(
        $url->getOption('routed_path') ?: $url->toString(),
        'GET',
        $currentRequest->query->all(),
        $currentRequest->cookies->all(),
        $currentRequest->files->all(),
        $currentRequest->server->all()
      );

      $request->attributes->set('graphql_subrequest', $batch);

      if ($session = $currentRequest->getSession()) {
        $request->setSession($session);
      }

      $this->httpKernel->handle($request);

      // TODO:
      // Remove the request stack manipulation once the core issue described at
      // https://www.drupal.org/node/2613044 is resolved.
      while ($this->requestStack->getCurrentRequest() === $request) {
        $this->requestStack->pop();
      }
    }
    else {
      static::processSubrequestBatch($batch);
    }

    $result = array_filter(array_map(function($item) {
      if ($item['field'] instanceof SubrequestFieldBase) {
        return $item['field']->getSubrequestResult();
      }

      return NULL;
    }, $batch));

    return $result;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($this->getPluginDefinition()['multi']) {
      foreach ($value as $item) {
        yield $item;
      }
    }
    else {
      yield $value;
    }
  }

}
