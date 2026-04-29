<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\GraphQL\DataProducer\Menu\MenuLink;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Url;
use Drupal\graphql\Attribute\DataProducer;
use Drupal\graphql\GraphQL\Execution\FieldContext;
use Drupal\graphql\Plugin\GraphQL\DataProducer\DataProducerPluginBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Returns the URL object of a menu link.
 *
 * @todo Fix input and output context type.
 */
#[DataProducer(
  id: 'menu_link_url',
  name: new TranslatableMarkup('Menu link url'),
  description: new TranslatableMarkup('Returns the URL of a menu link.'),
  produces: new ContextDefinition(
    data_type: 'any',
    label: new TranslatableMarkup('URL'),
  ),
  consumes: [
    'link' => new ContextDefinition(
      data_type: 'any',
      label: new TranslatableMarkup('Menu link'),
    ),
  ],
)]
class MenuLinkUrl extends DataProducerPluginBase implements ContainerFactoryPluginInterface {

  /**
   * {@inheritdoc}
   *
   * @codeCoverageIgnore
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
   * MenuLinkUrl constructor.
   */
  public function __construct(
    array $configuration,
    string $pluginId,
    array $pluginDefinition,
    protected LanguageManagerInterface $languageManager,
  ) {
    parent::__construct($configuration, $pluginId, $pluginDefinition);
  }

  /**
   * Resolver.
   */
  public function resolve(MenuLinkInterface $link, FieldContext $context): Url {
    $url = $link->getUrlObject();

    if ($langcode = $context->getContextLanguage()) {
      if ($language = $this->languageManager->getLanguage($langcode)) {
        $url->setOption('language', $language);
      }
      $context->addCacheContexts(['languages:language_url']);
    }

    return $url;
  }

}
