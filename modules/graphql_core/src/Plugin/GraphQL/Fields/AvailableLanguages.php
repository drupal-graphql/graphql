<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields;

use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\graphql_core\GraphQL\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * List site-wide configured languages.
 *
 * @GraphQLField(
 *   id = "available_languages_field",
 *   name = "availableLanguages",
 *   type = "Language",
 *   multi = true
 * )
 */
class AvailableLanguages extends FieldPluginBase implements ContainerFactoryPluginInterface {
  use DependencySerializationTrait;

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, LanguageManagerInterface $languageManager, ModuleHandlerInterface $moduleHandler) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);

    $this->languageManager = $languageManager;
    $this->moduleHandler = $moduleHandler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('language_manager'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    // This feature should probably be split into two: one field for returning
    // the languages of the site and one for the language switcher (which can
    // also be of different types: content, interface, etc).
    // At the moment, it is a bit of a mix: we get all the available languages
    // and we just invoke the language_switch_links alter hook.
    $allLanguages = $this->languageManager->getLanguages();
    $type = LanguageInterface::TYPE_INTERFACE;
    $url = Url::fromRoute('<front>');
    $this->moduleHandler->alter('language_switch_links', $allLanguages, $type, $url);

    foreach ($allLanguages as $language) {
      yield $language;
    }
  }

}
