<?php

namespace Drupal\graphql_block\Plugin\GraphQL\Fields;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\graphql_block\BlockResponse;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * List all blocks within a theme region.
 *
 * @GraphQLField(
 *   id = "blocks_by_region",
 *   name = "blocksByRegion",
 *   type = "Entity",
 *   types = {"Url"},
 *   multi = true,
 *   arguments = {
 *     "region" = "String"
 *   }
 * )
 */
class BlocksByRegion extends FieldPluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

  /**
   * The http kernel to issue subrequest.
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
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof Url) {
      $currentRequest = $this->requestStack->getCurrentRequest();

      $request = Request::create(
        $value->toString(),
        'GET',
        $currentRequest->query->all(),
        $currentRequest->cookies->all(),
        $currentRequest->files->all(),
        $currentRequest->server->all()
      );

      $request->attributes->set('graphql_block_region', $args['region']);

      if ($session = $currentRequest->getSession()) {
        $request->setSession($session);
      }

      $response = $this->httpKernel->handle($request, HttpKernelInterface::SUB_REQUEST);

      // TODO:
      // Remove the request stack manipulation once the core issue described at
      // https://www.drupal.org/node/2613044 is resolved.
      while ($this->requestStack->getCurrentRequest() === $request) {
        $this->requestStack->pop();
      }

      if ($response instanceof BlockResponse) {
        foreach ($response->getBlocks() as $block) {
          yield $block;
        }
      }
    }
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, HttpKernelInterface $httpKernel, RequestStack $requestStack) {
    $this->httpKernel = $httpKernel;
    $this->requestStack = $requestStack;

    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('http_kernel'),
      $container->get('request_stack')
    );
  }

}
