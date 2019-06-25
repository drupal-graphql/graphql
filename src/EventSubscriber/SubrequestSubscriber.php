<?php

namespace Drupal\graphql\EventSubscriber;

use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\Translator\TranslatorInterface;
use Drupal\language\ConfigurableLanguageManagerInterface;
use Drupal\language\LanguageNegotiatorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FinishRequestEvent;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;

class SubrequestSubscriber implements EventSubscriberInterface {

  /**
   * @var \Drupal\language\ConfigurableLanguageManagerInterface
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
   * Constructs a SubrequestSubscriber object.
   *
   * @param \Drupal\language\ConfigurableLanguageManagerInterface $languageManager
   * @param \Drupal\Core\StringTranslation\Translator\TranslatorInterface $translator
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   * @param \Drupal\language\LanguageNegotiatorInterface $languageNegotiator
   */
  public function __construct(ConfigurableLanguageManagerInterface $languageManager, TranslatorInterface $translator, AccountInterface $currentUser, LanguageNegotiatorInterface $languageNegotiator = NULL) {
    $this->languageManager = $languageManager;
    $this->translator = $translator;
    $this->currentUser = $currentUser;
    $this->languageNegotiator = $languageNegotiator;
  }

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

  /**
   * Handle kernel request events.
   *
   * @param \Symfony\Component\HttpKernel\Event\GetResponseEvent $event
   *   The kernel event object.
   */
  public function onKernelRequest(GetResponseEvent $event) {
    $request = $event->getRequest();
    if (!$request->attributes->has('_graphql_subrequest')) {
      return;
    }

    $request->attributes->set('_controller', '\Drupal\graphql\Controller\SubrequestExtractionController:extract');
    $this->resetLanguageContext($request);
  }

  /**
   * Handle kernel request finished events.
   *
   * @param \Symfony\Component\HttpKernel\Event\FinishRequestEvent $event
   *   The kernel event object.
   */
  public function onKernelRequestFinished(FinishRequestEvent $event) {
    $request = $event->getRequest();
    if (!$request->attributes->has('_graphql_subrequest')) {
      return;
    }

    $this->resetLanguageContext($request);
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      KernelEvents::REQUEST => 'onKernelRequest',
      KernelEvents::FINISH_REQUEST => 'onKernelRequestFinished',
    ];
  }

}
