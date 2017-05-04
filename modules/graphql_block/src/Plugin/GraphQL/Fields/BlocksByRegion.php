<?php

namespace Drupal\graphql_block\Plugin\GraphQL\Fields;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\graphql_block\BlockResponse;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * List all blocks within a theme region.
 *
 * @GraphQLField(
 *   id = "blocks_by_region",
 *   name = "blocksByRegion",
 *   type = "Block",
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
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof Url) {
      $request = Request::create($value->toString());
      $request->attributes->add([
        'graphql_block_region' => $args['region'],
      ]);

      $response = $this->httpKernel->handle($request, HttpKernelInterface::SUB_REQUEST);
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
  public function __construct(array $configuration, $pluginId, $pluginDefinition, HttpKernelInterface $httpKernel) {
    $this->httpKernel = $httpKernel;
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
      $container->get('http_kernel')
    );
  }

}
