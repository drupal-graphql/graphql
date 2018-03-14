<?php

namespace Drupal\graphql\Plugin\LanguageNegotiation;

use Drupal\language\LanguageNegotiationMethodBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class for identifying language from a selected language.
 *
 * @LanguageNegotiation(
 *   id = Drupal\graphql\Plugin\LanguageNegotiation\LanguageNegotiationGraphQL::METHOD_ID,
 *   weight = 12,
 *   name = @Translation("GraphQL context"),
 *   description = @Translation("The current GraphQL language context. Only available while executing a query.")
 * )
 */
class LanguageNegotiationGraphQL extends LanguageNegotiationMethodBase {

  /**
   * The language negotiation method id.
   */
  const METHOD_ID = 'language-graphql';

  /**
   * The current langcode.
   *
   * @var string|null
   */
  protected static $currentLangcode;

  /**
   * State if the language context is currently active.
   *
   * @var bool
   */
  protected static $contextActive;

  /**
   * Set the current context language.
   *
   * @param string $langcode
   *   The language to be set.
   */
  public static function setCurrentLanguage($langcode) {
    \Drupal::languageManager()->reset();
    static::$currentLangcode = $langcode;
    static::$contextActive = TRUE;
  }

  /**
   * Unset the current language.
   */
  public static function unsetCurrentLanguage() {
    static::$currentLangcode = NULL;
    static::$contextActive = FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function getLangcode(Request $request = NULL) {
    return static::$contextActive
      ? (static::$currentLangcode ?: \Drupal::languageManager()->getDefaultLanguage()->getId())
      : NULL;
  }

}
