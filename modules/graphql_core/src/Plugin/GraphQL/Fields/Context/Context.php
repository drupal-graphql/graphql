<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Context;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\Core\Url;
use Drupal\graphql\GraphQL\Buffers\SubRequestBuffer;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Request arbitrary drupal context objects with GraphQL.
 *
 * @GraphQLField(
 *   id = "context",
 *   secure = true,
 *   parents = {"Root", "InternalUrl"},
 *   deriver = "Drupal\graphql_core\Plugin\Deriver\Fields\ContextDeriver"
 * )
 */
class Context extends FieldPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The context repository service.
   *
   * @var \Drupal\Core\Plugin\Context\ContextRepositoryInterface
   */
  protected $contextRepository;

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
      $container->get('graphql.context_repository')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    SubRequestBuffer $subRequestBuffer,
    ContextRepositoryInterface $contextRepository
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->contextRepository = $contextRepository;
    $this->subRequestBuffer = $subRequestBuffer;
  }

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if (!($value instanceof Url)) {
      $value = Url::fromRoute('<front>');
    }

    $resolve = $this->subRequestBuffer->add($value, function () {
      $id = $this->getPluginDefinition()['context_id'];
      $contexts = $this->contextRepository->getRuntimeContexts([$id]);
      return isset($contexts[$id]) ? $contexts[$id]->getContextValue() : NULL;
    });

    return function ($value, array $args, ResolveInfo $info) use ($resolve) {
      yield $resolve();
    };
  }

}
