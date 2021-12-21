<?php

namespace Drupal\graphql\EventSubscriber;

use Drupal\language\ConfigurableLanguageManagerInterface;

/**
 * Sets the current language for the current request.
 */
trait CurrentLanguageResetTrait {

  /**
   * The language manager.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * The language negotiator.
   *
   * @var \Drupal\language\LanguageNegotiatorInterface|null
   */
  protected $languageNegotiator;

  /**
   * The translator.
   *
   * @var \Drupal\Core\StringTranslation\Translator\TranslatorInterface
   */
  protected $translator;

  /**
   * The current user service.
   *
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Resets the global language context across different services.
   */
  protected function resetLanguageContext(): void {
    if (!isset($this->languageNegotiator)) {
      return;
    }

    if (!$this->languageManager->isMultilingual()) {
      return;
    }

    $this->languageNegotiator->setCurrentUser($this->currentUser);
    if ($this->languageManager instanceof ConfigurableLanguageManagerInterface) {
      $this->languageManager->setNegotiator($this->languageNegotiator);
      $this->languageManager->setConfigOverrideLanguage($this->languageManager->getCurrentLanguage());
    }

    // After the language manager has initialized, set the default langcode for
    // the string translations.
    if (method_exists($this->translator, 'setDefaultLangcode')) {
      $language = $this->languageManager->getCurrentLanguage()->getId();
      $this->translator->setDefaultLangcode($language);
    }
  }

}
