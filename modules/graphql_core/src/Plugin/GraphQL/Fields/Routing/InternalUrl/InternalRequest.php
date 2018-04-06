<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Routing\InternalUrl;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\graphql\GraphQL\Buffers\SubRequestBuffer;
use Drupal\graphql\GraphQL\Cache\CacheableValue;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Issue an internal request and retrieve the response object.
 *
 * @GraphQLField(
 *   id = "internal_url_request",
 *   secure = true,
 *   name = "request",
 *   type = "InternalResponse",
 *   parents = {"InternalUrl"}
 * )
 */
class InternalRequest extends FieldPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The http kernel service.
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
   * The subrequest buffer service.
   *
   * @var \Drupal\graphql\GraphQL\Buffers\SubRequestBuffer
   */
  protected $subRequestBuffer;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('graphql.buffer.subrequest'),
      $container->get('http_kernel'),
      $container->get('request_stack')
    );
  }

  /**
   * InternalRequest constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition array.
   * @param \Drupal\graphql\GraphQL\Buffers\SubRequestBuffer $subRequestBuffer
   *   The sub-request buffer service.
   * @param \Symfony\Component\HttpKernel\HttpKernelInterface $httpKernel
   *   The http kernel.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    SubRequestBuffer $subRequestBuffer,
    HttpKernelInterface $httpKernel,
    RequestStack $requestStack
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->subRequestBuffer = $subRequestBuffer;
    $this->httpKernel = $httpKernel;
    $this->requestStack = $requestStack;
  }

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof Url) {
      $resolve = $this->subRequestBuffer->add($value, function () {
        $request = $this->requestStack->getCurrentRequest()->duplicate();
        $request->attributes->set('_controller', $request->get('_graphql_controller'));
        $request->attributes->remove('_graphql_subrequest');
        $request->attributes->remove('_graphql_controller');

        $response = $this->httpKernel->handle($request, HttpKernelInterface::SUB_REQUEST);

        // TODO:
        // Remove the request stack manipulation once the core issue described at
        // https://www.drupal.org/node/2613044 is resolved.
        while ($this->requestStack->getCurrentRequest() === $request) {
          $this->requestStack->pop();
        }

        return $response;
      });

      return function ($value, array $args, ResolveContext $context, ResolveInfo $info) use ($resolve) {
        /** @var \Drupal\graphql\GraphQL\Cache\CacheableValue $response */
        $response = $resolve();
        yield new CacheableValue($response->getValue(), [$response]);
      };
    }
  }

}