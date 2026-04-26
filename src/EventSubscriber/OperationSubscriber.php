<?php

declare(strict_types=1);

namespace Drupal\graphql\EventSubscriber;

use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\Translator\TranslatorInterface;
use Drupal\graphql\Event\OperationEvent;
use Drupal\graphql\Plugin\LanguageNegotiation\OperationLanguageNegotiation;
use Drupal\language\LanguageNegotiatorInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Sets the language before/after a GraphQL operation.
 */
class OperationSubscriber implements EventSubscriberInterface {

  use CurrentLanguageResetTrait;

  /**
   * Constructs a OperationSubscriber object.
   *
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $languageManager
   *   The language manager service.
   * @param \Drupal\Core\StringTranslation\Translator\TranslatorInterface $translator
   *   The string translation service.
   * @param \Drupal\Core\Session\AccountInterface $currentUser
   *   The current user.
   * @param \Drupal\language\LanguageNegotiatorInterface|null $languageNegotiator
   *   (optional) The language negotiator service.
   */
  public function __construct(
    protected ModuleHandlerInterface $moduleHandler,
    protected LanguageManagerInterface $languageManager,
    protected TranslatorInterface $translator,
    protected AccountInterface $currentUser,
    protected ?LanguageNegotiatorInterface $languageNegotiator = NULL,
  ) {
  }

  /**
   * Handle operation start events.
   *
   * @param \Drupal\graphql\Event\OperationEvent $event
   *   The kernel event object.
   */
  public function onBeforeOperation(OperationEvent $event): void {
    if ($this->moduleHandler->moduleExists('language') && !empty($this->languageNegotiator)) {
      OperationLanguageNegotiation::setContext($event->getContext());
    }

    $this->resetLanguageContext();
  }

  /**
   * Handle operation end events.
   *
   * @param \Drupal\graphql\Event\OperationEvent $event
   *   The kernel event object.
   */
  public function onAfterOperation(OperationEvent $event): void {
    if ($this->moduleHandler->moduleExists('language') && !empty($this->languageNegotiator)) {
      OperationLanguageNegotiation::setContext($event->getContext());
    }

    $this->resetLanguageContext();
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents(): array {
    return [
      OperationEvent::GRAPHQL_OPERATION_BEFORE => 'onBeforeOperation',
      OperationEvent::GRAPHQL_OPERATION_AFTER => 'onAfterOperation',
    ];
  }

}
