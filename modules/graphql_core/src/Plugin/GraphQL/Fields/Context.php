<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\Plugin\Context\ContextRepositoryInterface;
use Drupal\graphql_core\BatchedFieldResolver;
use Drupal\graphql_core\GraphQL\SubrequestField;
use Drupal\graphql_core\GraphQLSchemaManagerInterface;
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
 *   types = {"Url", "Root"},
 *   nullable = true,
 *   deriver = "\Drupal\graphql_core\Plugin\Deriver\ContextDeriver"
 * )
 */
class Context extends SubrequestField {

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
      $container->get('graphql_core.batched_resolver'),
      $container->get('graphql_core.context_repository')
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
  protected function buildType(GraphQLSchemaManagerInterface $schemaManager) {
    if ($this instanceof PluginInspectionInterface) {
      $definition = $this->getPluginDefinition();
      if (array_key_exists('data_type', $definition) && $definition['data_type']) {
        $types = $schemaManager->find(function ($def) use ($definition) {
          return array_key_exists('data_type', $def) && $def['data_type'] == $definition['data_type'];
        }, [
          GRAPHQL_CORE_TYPE_PLUGIN,
          GRAPHQL_CORE_INTERFACE_PLUGIN,
          GRAPHQL_CORE_SCALAR_PLUGIN,
        ]);

        return array_pop($types) ?: $schemaManager->findByName('String', [GRAPHQL_CORE_SCALAR_PLUGIN]);
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
