<?php

namespace Drupal\graphql\GraphQL\Context\Handlers;

use Drupal\Core\Language\LanguageManagerInterface;

// TODO: Manage context setting side effects.
class LanguageContextHandler {

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * LanguageContextHandler constructor.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager service.
   */
  public function __construct(LanguageManagerInterface $languageManager) {
    $this->languageManager = $languageManager;
  }

  /**
   * @return string
   */
  public function getCurrentLanguage() {
    return $this->languageManager->getDefaultLanguage()->getId();
  }

}
