<?php

namespace Drupal\graphql_json\Plugin\GraphQL\Fields;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Annotation\GraphQLField;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Serialize entities to json.
 *
 * @GraphQLField(
 *   id = "entity_to_json",
 *   name = "toJson",
 *   type = "JsonObject",
 *   types = {"Entity"}
 * )
 */
class EntityToJson extends FieldPluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

  /**
   * The serializer.
   *
   * @var \Symfony\Component\Serializer\SerializerInterface
   */
  protected $serializer;

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    if ($container->get('module_handler')->moduleExists('serialization')) {
      return new static(
        $configuration,
        $plugin_id,
        $plugin_definition,
        $container->get('serializer')
      );
    }
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    SerializerInterface $serializer
  ) {
    $this->serializer = $serializer;
    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }


  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    yield json_decode($this->serializer->serialize($value, 'json'), TRUE);
  }


}