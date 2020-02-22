<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Routing\InternalUrl;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\path_alias\AliasManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * @GraphQLField(
 *   id = "internal_url_path_alias",
 *   secure = true,
 *   name = "pathAlias",
 *   description = @Translation("The url's path alias if any."),
 *   response_cache_contexts = {"languages:language_url"},
 *   type = "String",
 *   parents = {"InternalUrl"}
 * )
 */
class Alias extends FieldPluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

  /**
   * Instance of an alias manager.
   *
   * @var \Drupal\path_alias\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('path_alias.manager')
    );
  }

  /**
   * Alias constructor.
   *
   * @param array $configuration
   *   The plugin configuration array.
   * @param string $pluginId
   *   The plugin id.
   * @param mixed $pluginDefinition
   *   The plugin definition.
   * @param \Drupal\path_alias\AliasManagerInterface $aliasManager
   *   The alias manager service
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    AliasManagerInterface $aliasManager
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->aliasManager = $aliasManager;
  }


  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof Url) {
      $internal = "/{$value->getInternalPath()}";
      $alias = $this->aliasManager->getAliasByPath($internal);

      // If the fetched alias is identical to the internal path, it means we do
      // not have a configured alias for this path.
      if ($internal !== $alias) {
        yield $alias;
      }
    }
  }

}
