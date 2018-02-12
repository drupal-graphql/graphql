<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Routing\InternalUrl;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Path\AliasManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\graphql\GraphQL\Cache\CacheableValue;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * @GraphQLField(
 *   id = "internal_url_path_alias",
 *   secure = true,
 *   name = "pathAlias",
 *   description = @Translation("The url's path alias if any."),
 *   type = "String",
 *   parents = {"InternalUrl"},
 *   arguments = {
 *     "language" = "LanguageId"
 *   }
 * )
 */
class Alias extends FieldPluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

  /**
   * Instance of an alias manager.
   *
   * @var \Drupal\Core\Path\AliasManagerInterface
   */
  protected $aliasManager;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $pluginId, $pluginDefinition) {
    return new static(
      $configuration,
      $pluginId,
      $pluginDefinition,
      $container->get('path.alias_manager'),
      $container->get('language_manager')
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
   * @param \Drupal\Core\Path\AliasManagerInterface $aliasManager
   *   The alias manager service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager service.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    AliasManagerInterface $aliasManager,
    LanguageManagerInterface $languageManager
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->aliasManager = $aliasManager;
    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof Url) {
      $internal = "/{$value->getInternalPath()}";
      $language = isset($args['language']) ? $this->languageManager->getLanguage($args['language']) : NULL;
      $alias = $this->aliasManager->getAliasByPath($internal, $language);

      // If the fetched alias is identical to the internal path, it means we do
      // not have a configured alias for this path.
      if ($internal !== $alias) {
        yield $alias;
      }
    }
  }

}
