<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\graphql_core\ContextResponse;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Drupal\graphql_core\GraphQLSchemaManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\HttpKernelInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Request arbitrary drupal context objects with GraphQL.
 *
 * @GraphQLField(
 *   id = "context",
 *   types = {"Url"},
 *   nullable = true,
 *   deriver = "\Drupal\graphql_core\Plugin\Deriver\ContextDeriver"
 * )
 */
class Context extends FieldPluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

  /**
   * A http kernel to issue sub-requests to.
   *
   * @var \Symfony\Component\HttpKernel\HttpKernelInterface
   */
  protected $httpKernel;

  /**
   * A language manager to reset language negotiation before resolving contexts.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

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
  public function __construct(array $configuration, $pluginId, $pluginDefinition, HttpKernelInterface $httpKernel, LanguageManagerInterface $languageManager) {
    $this->httpKernel = $httpKernel;
    $this->languageManager = $languageManager;
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
      $container->get('language_manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof Url) {
      $contextId = $this->getPluginDefinition()['context_id'];

      $request = Request::create($value->getOption('routed_path') ?: $value->toString());
      $request->attributes->add([
        'graphql_context' => $contextId,
      ]);

      // TODO:
      // Language manager stores negotiation state between requests.
      //
      // This is a hack to ensure language get's re-negotiated.
      // This will issue affect other sub-requests and potentially other
      // services that keep a static cache.
      $this->languageManager->reset();

      $response = $this->httpKernel->handle($request, HttpKernelInterface::SUB_REQUEST);
      if ($response instanceof ContextResponse) {
        yield $response->getContext()->getContextValue();
      }
    }

  }

}
