<?php

namespace Drupal\graphql_core\Plugin\LanguageNegotiation;

use Drupal\language\LanguageNegotiationMethodBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class for identifying language for api calls.
 *
 * @LanguageNegotiation(
 *   id = \Drupal\graphql_core\Plugin\LanguageNegotiation\LanguageNegotiationGraphQL::METHOD_ID,
 *   weight = -10,
 *   name = @Translation("GraphQL API"),
 *   description = @Translation("Language for GraphQL API based on get parameter."),
 * )
 */
class LanguageNegotiationGraphQL extends LanguageNegotiationMethodBase {

  /**
   * The language negotiation method id.
   */
  const METHOD_ID = 'language-graphql';

  /**
   * {@inheritdoc}
   */
  public function getLangcode(Request $request = NULL) {
    $langcode = NULL;

    if ($this->languageManager && $request && $request->query->has('graphqlLanguage')) {
      $langcode = $request->query->get('graphqlLanguage');
    }

    return $langcode;
  }

}
