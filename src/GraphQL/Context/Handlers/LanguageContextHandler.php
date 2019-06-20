<?php

namespace Drupal\graphql\GraphQL\Context\Handlers;

use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\language\LanguageNegotiationMethodBase;
use Drupal\language\LanguageNegotiatorInterface;
use Symfony\Component\HttpFoundation\Request;

// TODO: Manage context setting side effects.
class LanguageContextHandler extends LanguageNegotiationMethodBase {

  const METHOD_ID = 'language-graphql';

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $manager;

  /**
   * The language negotiator service.
   *
   * @var \Drupal\language\LanguageNegotiatorInterface
   */
  protected $negotiator;

  /**
   * LanguageContextHandler constructor.
   *
   * @param \Drupal\Core\Language\LanguageManagerInterface $manager
   *   The language manager service.
   */
  public function __construct(LanguageManagerInterface $manager) {
    $this->manager = $manager;
  }

  /**
   * @param \Drupal\language\LanguageNegotiatorInterface $negotiator
   *
   * @return $this
   */
  public function setNegotiator(LanguageNegotiatorInterface $negotiator) {
    $this->negotiator = $negotiator;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getLangcode(Request $request = NULL) {
    return $this->manager->getDefaultLanguage()->getId();
  }
}
