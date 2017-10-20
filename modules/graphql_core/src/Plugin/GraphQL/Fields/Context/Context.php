<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Context;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\graphql\GraphQL\Batching\BatchedFieldResolver;
use Drupal\graphql\Plugin\GraphQL\Fields\SubrequestFieldBase;
use Drupal\graphql\Plugin\GraphQL\SchemaBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Request arbitrary drupal context objects with GraphQL.
 *
 * TODO: Move this to `InternalUrl` (breaking change).
 *
 * @GraphQLField(
 *   id = "context",
 *   secure = true,
 *   parents = {"Url", "Root"},
 *   nullable = true,
 *   deriver = "Drupal\graphql_core\Plugin\Deriver\Fields\ContextDeriver"
 * )
 */
class Context extends SubrequestFieldBase {

  /**
   * The context repository.
   *
   * @var \Drupal\Core\Plugin\Context\ContextRepositoryInterface
   */
  protected $contextRepository;

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
      $container->get('graphql.batched_resolver'),
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
    HttpKernelInterface $httpKernel,
    RequestStack $requestStack,
    BatchedFieldResolver $batchedFieldResolver,
    ContextRepositoryInterface $contextRepository
  ) {
    $this->contextRepository = $contextRepository;
    parent::__construct($configuration, $pluginId, $pluginDefinition, $httpKernel, $requestStack, $batchedFieldResolver);
  }

  /**
   * {@inheritdoc}
   */
  protected function buildType(SchemaBuilderInterface $schemaManager) {
    if ($this instanceof PluginInspectionInterface) {
      $definition = $this->getPluginDefinition();
      if (array_key_exists('data_type', $definition) && $definition['data_type']) {
        return $schemaManager->findByDataType($definition['data_type']) ?: $schemaManager->findByName('String', [GRAPHQL_SCALAR_PLUGIN]);
      }
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    $definition = $this->getPluginDefinition();
    return parent::resolve($value, [
      'context' => $definition['context_id'],
    ], $info);
  }

  /**
   * {@inheritdoc}
   */
  protected function resolveSubrequest($value, array $args, ResolveInfo $info) {
    $contextId = $args['context'];
    $contexts = $this->contextRepository->getRuntimeContexts([$args['context']]);
    return isset($contexts[$contextId]) ? $contexts[$contextId]->getContextValue() : NULL;
  }

}
