<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Fields\LanguageSwitch;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Url;
use Drupal\graphql\GraphQL\Buffers\SubRequestBuffer;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\Plugin\GraphQL\Fields\FieldPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * @GraphQLField(
 *   id = "url_language_switch_links",
 *   secure = true,
 *   name = "languageSwitchLinks",
 *   type = "[LanguageSwitchLink]",
 *   parents = {"InternalUrl"},
 *   arguments = {
 *     "language" = "LanguageId"
 *   },
 *   response_cache_contexts = {
 *     "languages:language_url",
 *     "languages:language_interface",
 *   },
 *   contextual_arguments = {"language"}
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
   * The subrequest buffer service.
   *
   * @var \Drupal\graphql\GraphQL\Buffers\SubRequestBuffer
   */
  protected $subRequestBuffer;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('language_manager'),
      $container->get('graphql.buffer.subrequest'),
      $container->get('config.factory')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function __construct(
    array $configuration,
    $pluginId,
    $pluginDefinition,
    LanguageManagerInterface $languageManager,
    SubRequestBuffer $subRequestBuffer,
    ConfigFactoryInterface $configFactory
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
    $this->languageManager = $languageManager;
    $this->subRequestBuffer = $subRequestBuffer;
    $this->configFactory = $configFactory;
  }

  /**
   * {@inheritdoc}
   */
  protected function resolveValues($value, array $args, ResolveContext $context, ResolveInfo $info) {
    if ($value instanceof Url) {

      // Use the <front> route if the requested url is the frontpage.
      $frontpage = $this->configFactory->get('system.site')->get('page.front');
      if ('/' . $value->getInternalPath() === $frontpage) {
        $value = Url::fromRoute('<front>');
      }

      $links = $this->languageManager->getLanguageSwitchLinks(LanguageInterface::TYPE_URL, $value);

      $current = $this->languageManager->getLanguage($args['language']);
      if (!$current) {
        $current = $this->languageManager->getDefaultLanguage();
      }

      if (!empty($links->links)) {
        foreach ($links->links as $link) {
          // Yield the link array and the language object of the language
          // context resolved from the sub-request.
          yield [
            'link' => $link,
            'context' => $current,
          ];
        }
      }
    }
  }

}
