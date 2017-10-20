<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Enums\Languages;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Plugin\GraphQL\Enums\EnumPluginBase;
use Drupal\graphql\Plugin\GraphQL\SchemaBuilderInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Generates an enumeration of numbers.
 *
 * @GraphQLEnum(
 *   id = "available_languages",
 *   name = "AvailableLanguages"
 * )
 */
class AvailableLanguages extends EnumPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, LanguageManagerInterface $languageManager) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);

    $this->languageManager = $languageManager;
  }

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
   * {@inheritdoc}
   */
  public function buildValues(SchemaBuilderInterface $schemaManager) {
    $values = [];

    foreach ($this->languageManager->getLanguages() as $language) {
      $values[] = [
        'name' => str_replace('-', '_', $language->getId()),
        'value' => $language->getId(),
      ];
    }

    return $values;
  }

}
