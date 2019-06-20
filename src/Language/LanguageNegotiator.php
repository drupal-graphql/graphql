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
    $language = $this->context->getLangcode();

    if (isset($languages[$language])) {
      $this->context->persist($languages[$language]);

      $method = LanguageContextHandler::METHOD_ID;
      return [$method => $languages[$language]];
    }

    $method = LanguageNegotiatorInterface::METHOD_ID;
    return [$method => $this->manager->getDefaultLanguage()];
  }

  /**
   * {@inheritdoc}
   */
  public function getNegotiationMethods($type = NULL) {
    $method = LanguageContextHandler::METHOD_ID;
    $class = LanguageContextHandler::class;

    return [$method => ['class' => $class]];
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

    throw new \LogicException('Invalid language negotiation method.');
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
