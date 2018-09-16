<?php

namespace Drupal\graphql\Language;

use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Simple service that stores the current GraphQL language state.
 */
class LanguageContext {

  /**
   * Indicates if the GraphQL context language is currently active.
   *
   * @var bool
   */
  protected $isActive = FALSE;

  /**
   * The current language context.
   *
   * @var string
   */
  protected $currentLanguage = NULL;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * LanguageContext constructor.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager service.
   */
  public function __construct(LanguageManagerInterface $languageManager) {
    $this->languageManager = $languageManager;
  }

  /**
   * Retrieve the current language.
   *
   * @return string|null
   *   The current language code, or null if the context is not active.
   */
  public function getCurrentLanguage() {
    return $this->isActive
      ? ($this->currentLanguage ?: $this->languageManager->getDefaultLanguage()->getId())
      : NULL;
  }

  /**
   * Executes a callable in a defined language context.
   *
   * @param callable $callable
   *   The callable to be executed.
   * @param string $language
   *   The langcode to be set.
   *
   * @return mixed
   *   The callables result.
   *
   * @throws \Exception
   *   Any exception caught while executing the callable.
   */
  public function executeInLanguageContext(callable $callable, $language) {
    $languageBefore = $this->currentLanguage;
    $activeBefore = $this->isActive;

    $this->currentLanguage = $language;
    $this->isActive = TRUE;
    $this->languageManager->reset();
    // Extract the result array.
    try {
      return call_user_func($callable);
    }
    catch (\Exception $exception) {
      throw $exception;
    }
    finally {
      // In any case, set the language context back to its previous values.
      $this->currentLanguage = $languageBefore;
      $this->isActive = $activeBefore;
      $this->languageManager->reset();
    }
  }

}
