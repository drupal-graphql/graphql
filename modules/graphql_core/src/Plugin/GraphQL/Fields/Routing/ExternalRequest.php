<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Routing;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\graphql\Annotation\GraphQLField;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use GuzzleHttp\ClientInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Issue an external request and retrieve the response object.
 *
 * @GraphQLField(
 *   id = "external_request",
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
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    ClientInterface $httpClient
  ) {
    $this->httpClient = $httpClient;
    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }


  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof Url) {
      yield $this->httpClient->request('GET', $value->toString());
    }
  }

}
