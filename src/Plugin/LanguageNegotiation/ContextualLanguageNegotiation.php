<?php

namespace Drupal\graphql\Plugin\LanguageNegotiation;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\Language\LanguageContext;
use Drupal\language\LanguageNegotiationMethodBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class for identifying language from a selected language.
 *
 * @LanguageNegotiation(
 *   id = Drupal\graphql\Plugin\LanguageNegotiation\ContextualLanguageNegotiation::METHOD_ID,
 *   weight = -999,
 *   name = @Translation("GraphQL context"),
 *   description = @Translation("The current GraphQL language context. Only available while executing a query.")
 * )
 */
class ContextualLanguageNegotiation extends LanguageNegotiationMethodBase implements ContainerFactoryPluginInterface {

  /**
   * The graphql language context.
   *
   * @var \Drupal\graphql\Language\LanguageContext
   */
  protected $languageContext;

  /**
   * {@inheritdoc}
   */
  public static function create(
    ContainerInterface $container,
    array $configuration,
    $plugin_id,
    $plugin_definition
  ) {
    return new static($container->get('graphql.language_context'));
  }

  /**
   * LanguageNegotiationGraphQL constructor.
   *
   * @param \Drupal\graphql\Language\LanguageContext $languageContext
   *   Instance of the graphql language context.
   */
  public function __construct(LanguageContext $languageContext) {
    $this->languageContext = $languageContext;
  }

  /**
   * The language negotiation method id.
   */
  const METHOD_ID = 'language-graphql';

  /**
   * {@inheritdoc}
   */
  public function getLangcode(Request $request = NULL) {
    return $this->languageContext->getCurrentLanguage();
  }

}
