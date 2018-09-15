<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Routing\ExternalUrl;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Issue an external request and retrieve the response object.
 *
 * @GraphQLField(
 *   id = "external_url_request",
 *   name = "request",
 *   type = "ExternalResponse",
 *   parents = {"ExternalUrl"}
 * )
 */
class ExternalRequest extends FieldPluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

  /**
   * @var \GuzzleHttp\ClientInterface
   */
  protected $httpClient;

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('http_client')
    );
  }

  /**
   * ExternalRequest constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition array.
   * @param \GuzzleHttp\ClientInterface $httpClient
   *   The http client service.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    ClientInterface $httpClient
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->httpClient = $httpClient;
  }


  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof Url) {
      yield $this->httpClient->request('GET', $value->toString());
    }
  }

}
