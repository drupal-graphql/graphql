<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Routing;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * @GraphQLField(
 *   id = "url_translate",
 *   secure = true,
 *   name = "translate",
 *   description = @Translation("The translated url object."),
 *   type = "Url",
 *   parents = {"Url"},
 *   arguments = {
 *     "language" = "LanguageId!"
 *   },
 *   contextual_arguments = {"language"}
 * )
 */
class Translate extends FieldPluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

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
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager service.
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    LanguageManagerInterface $languageManager
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof Url) {
      $language = $this->languageManager->getLanguage($args['language']);
      $url = clone $value;

      // The routed path is cached in the original object. Clear it.
      if ($url->isRouted() && $url->getOption('routed_path')) {
        $url->setOption('routed_path', NULL);
      }

      yield $url->setOption('language', $language);
    }
  }

}
