<?php

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Context;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Cache\RefinableCacheableDependencyInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\Core\Url;
use Drupal\graphql\Annotation\DataProducer;
use Drupal\graphql\GraphQL\Buffers\SubRequestBuffer;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use GraphQL\Deferred;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Request arbitrary drupal context objects with GraphQL.
 *
 * @DataProducer(
 *   id = "context",
 *   name = @Translation("Context"),
 *   description = @Translation("Retrieve a given requests context."),
 *   produces = @ContextDefinition("any",
 *     label = @Translation("Context value")
 *   ),
 *   consumes = {
 *     "url" = @ContextDefinition("any",
 *       label = @Translation("Url")
 *     ),
 *     "context" = @ContextDefinition("string",
 *       label = @Translation("Context")
 *     ),
 *   }
 * )
 */
class Context extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

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
   * Context constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition.
   * @param \Drupal\graphql\GraphQL\Buffers\SubRequestBuffer $subRequestBuffer
   *   The sub-request buffer service.
   * @param \Drupal\Core\Plugin\Context\ContextRepositoryInterface $contextRepository
   *   The context repository service.
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
   * @param \Drupal\Core\Url $url
   * @param string $id
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   *
   * @return string
   */
  public function resolve(Url $url, $id, RefinableCacheableDependencyInterface $metadata) {
    if (is_null($url)) {
      return $this->resolveContext($id, $metadata);
    }

    $resolver =  $this->subRequestBuffer->add($url, function () use ($metadata, $id) {
      return $this->resolveContext($id, $metadata);
    });

    return new Deferred(function () use ($resolver) {
      return $resolver();
    });
  }

  /**
   * @param $id
   * @param \Drupal\Core\Cache\RefinableCacheableDependencyInterface $metadata
   *
   * @return mixed|null
   */
  protected function resolveContext($id, RefinableCacheableDependencyInterface $metadata) {
    $contexts = $this->contextRepository->getRuntimeContexts([$id]);
    $value = isset($contexts[$id]) ? $contexts[$id]->getContextValue() : NULL;
    if ($value instanceof CacheableDependencyInterface) {
      $metadata->addCacheableDependency($value);
    }
    return $value;
  }

}
