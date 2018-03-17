<?php

namespace Drupal\graphql\Plugin\LanguageNegotiation;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\graphql\GraphQLLanguageContext;
use Drupal\language\LanguageNegotiationMethodBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class for identifying language from a selected language.
 *
 * @LanguageNegotiation(
 *   id = Drupal\graphql\Plugin\LanguageNegotiation\LanguageNegotiationGraphQL::METHOD_ID,
 *   weight = -999,
 *   name = @Translation("GraphQL context"),
 *   description = @Translation("The current GraphQL language context. Only available while executing a query.")
 * )
 */
class LanguageNegotiationGraphQL extends LanguageNegotiationMethodBase implements ContainerFactoryPluginInterface {

  /**
   * The graphql language context.
   *
   * @var \Drupal\graphql\GraphQLLanguageContext
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
   * @param \Drupal\graphql\GraphQLLanguageContext $languageContext
   *   Instance of the GraphQL language context.
   */
  public function __construct(GraphQLLanguageContext $languageContext) {
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
