<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields;

use Drupal\Component\Plugin\PluginInspectionInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
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
 *   id="context",
 *   types={"Url"},
 *   nullable=true,
 *   deriver="\Drupal\graphql_core\Plugin\Deriver\ContextDeriver"
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
   * {@inheritdoc}
   */
  protected function buildType(GraphQLSchemaManagerInterface $schemaManager) {
    if ($this instanceof PluginInspectionInterface) {
      $definition = $this->getPluginDefinition();
      if (array_key_exists('dataType', $definition) && $definition['dataType']) {
        $types = $schemaManager->find(function ($def) use ($definition) {
          return array_key_exists('dataType', $def) && $def['dataType'] == $definition['dataType'];
        }, [
          GRAPHQL_CORE_TYPE_PLUGIN,
          GRAPHQL_CORE_INTERFACE_PLUGIN,
          GRAPHQL_CORE_SCALAR_PLUGIN,
        ]);
        $type = array_pop($types);
        if (empty($type)) {
          $type = $schemaManager->findByName('String', [GRAPHQL_CORE_SCALAR_PLUGIN]);
        }
        return $type;
      }
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, HttpKernelInterface $httpKernel) {
    $this->httpKernel = $httpKernel;
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('http_kernel')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof Url) {
      $context_id = $this->getPluginDefinition()['context_id'];

      // TODO: Pass url object directly to not re-run full routing?
      $request = Request::create($value->toString());
      $request->attributes->add([
        'graphql_context' => $context_id,
      ]);

      $response = $this->httpKernel->handle($request, HttpKernelInterface::SUB_REQUEST);
      if ($response instanceof ContextResponse) {
        yield $response->getContext()->getContextValue();
      }
    }

  }

}
