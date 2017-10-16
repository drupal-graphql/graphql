<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\Routing;

use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * @GraphQLField(
 *   id = "url_language_switch_links",
 *   secure = true,
 *   name = "languageSwitchLinks",
 *   multi = true,
 *   type = "LanguageSwitchLink",
 *   types = {"Url"}
 * )
 */
class LanguageSwitchLinks extends FieldPluginBase implements ContainerFactoryPluginInterface {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('language_manager'));
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(array $configuration, $pluginId, $pluginDefinition, LanguageManagerInterface $languageManager) {
    $this->languageManager = $languageManager;
    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveInfo $info) {
    if ($value instanceof Url) {
      $links = $this->languageManager->getLanguageSwitchLinks(LanguageInterface::TYPE_URL, $value);
      if (!empty($links->links)) {
        $current_langcode = $this->languageManager->getCurrentLanguage(LanguageInterface::TYPE_URL)->getId();
        foreach ($links->links as $link) {
          /** @var \Drupal\Core\Url $url */
          $url = $link['url'];
          /** @var \Drupal\Core\Language\Language $language */
          $language = $link['language'];
          $langcode = $language->getId();

          yield [
            'langcode' => $langcode,
            'url' => $url->setOption('language', $language),
            'title' => $link['title'],
            'isActive' => $langcode === $current_langcode,
          ];
        }
      }
    }
  }

}
