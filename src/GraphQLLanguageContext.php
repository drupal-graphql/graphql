<?php

namespace Drupal\graphql;

use Drupal\Core\Language\LanguageManagerInterface;

/**
 * Simple service that stores the current GraphQL language state.
 */
class GraphQLLanguageContext {

  /**
   * Indicates if the GraphQL context language is currently active.
   *
   * @var bool
   */
  protected $isActive;

  /**
   * The current language context.
   *
   * @var string
   */
  protected $currentLanguage;

  /**
   * @var \SplStack
   */
  protected $languageStack;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * GraphQLLanguageContext constructor.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager service.
   */
  public function __construct(LanguageManagerInterface $languageManager) {
    $this->languageManager = $languageManager;
    $this->languageStack = new \SplStack();
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
    $this->languageStack->push($this->currentLanguage);
    $this->currentLanguage = $language;
    $this->isActive = TRUE;
    $this->languageManager->reset();
    // Extract the result array.
    try {
      return call_user_func($callable);
    }
    catch (\Exception $exc) {
      throw $exc;
    }
    finally {
      // In any case, set the language context back to null.
      $this->currentLanguage = $this->languageStack->pop();
      $this->isActive = FALSE;
      $this->languageManager->reset();
    }
  }

}
