<?php

namespace Drupal\graphql\EventSubscriber;

use Drupal\language\ConfigurableLanguageManagerInterface;

trait CurrentLanguageResetTrait {

  /**
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * @var \Drupal\language\LanguageNegotiatorInterface
   */
  protected $languageNegotiator;

  /**
   * @var \Drupal\Core\StringTranslation\Translator\TranslatorInterface
   */
  protected $translator;

  /**
   * @var \Drupal\Core\Session\AccountInterface
   */
  protected $currentUser;

  /**
   * Resets the global language context across different services.
   */
  protected function resetLanguageContext() {
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