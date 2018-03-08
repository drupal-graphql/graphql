<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Languages;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * List site-wide configured languages.
 *
 * @GraphQLField(
 *   id = "available_languages_field",
 *   secure = true,
 *   name = "availableLanguages",
 *   description = @Translation("Loads the list of available languages."),
 *   type = "[Language]"
 * )
 */
class AvailableLanguages extends FieldPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The language manager.
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
   * AvailableLanguages constructor.
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
  public function __construct(array $configuration, $pluginId, $pluginDefinition, LanguageManagerInterface $languageManager) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->languageManager = $languageManager;
  }

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    foreach ($this->languageManager->getLanguages() as $language) {
      yield $language;
    }
  }

}
