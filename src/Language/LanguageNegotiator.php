<?php

namespace Drupal\graphql\Language;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\graphql\GraphQL\Context\Handlers\LanguageContextHandler;
use Drupal\language\LanguageNegotiatorInterface;

class LanguageNegotiator implements LanguageNegotiatorInterface {

  /**
   * @var \Drupal\graphql\GraphQL\Context\Handlers\LanguageContextHandler
   */
  protected $context;

  /**
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $manager;

  /**
   * LanguageNegotiator constructor.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $manager
   */
  public function __construct(LanguageManagerInterface $manager) {
    $this->manager = $manager;
  }

  /**
   * @param \Drupal\graphql\GraphQL\Context\Handlers\LanguageContextHandler $context
   *
   * @return $this
   */
  public function setContext(LanguageContextHandler $context) {
    $this->context = $context;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function initializeType($type) {
    $languages = $this->manager->getLanguages();
    $langcode = $this->context->getLangcode();
    $language = isset($languages[$langcode]) ? $languages[$langcode] : NULL;

    if (!empty($language)) {
      $this->context->persist($language);
      return [LanguageContextHandler::METHOD_ID => $language];
    }

    return [LanguageNegotiatorInterface::METHOD_ID => $this->manager->getDefaultLanguage()];
  }

  /**
   * {@inheritdoc}
   */
  public function getNegotiationMethods($type = NULL) {
    return [LanguageContextHandler::METHOD_ID => [
      'class' => LanguageContextHandler::class,
    ]];
  }

  /**
   * {@inheritdoc}
   */
  public function getNegotiationMethodInstance($method) {
    if ($method === LanguageContextHandler::METHOD_ID) {
      return $this->context;
    }

    throw new \LogicException('Invalid language negotiation method.');
  }

  /**
   * {@inheritdoc}
   */
  public function getPrimaryNegotiationMethod($type) {
    return LanguageContextHandler::METHOD_ID;
  }

  /**
   * {@inheritdoc}
   */
  public function isNegotiationMethodEnabled($method, $type = NULL) {
    if ($method === LanguageContextHandler::METHOD_ID) {
      return TRUE;
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function reset() {
    // Nothing to do here.
  }

  /**
   * {@inheritdoc}
   */
  public function setCurrentUser(AccountInterface $currentUser) {
    // Nothing to do here.
  }

  /**
   * {@inheritdoc}
   */
  public function saveConfiguration($type, $enabled_methods) {
    // Nothing to do here.
  }

  /**
   * {@inheritdoc}
   */
  public function purgeConfiguration() {
    // Nothing to do here.
  }

  /**
   * {@inheritdoc}
   */
  public function updateConfiguration(array $types) {
    // Nothing to do here.
  }
}
