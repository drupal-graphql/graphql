<?php

namespace Drupal\graphql\Language;

use Drupal\language\LanguageNegotiator as CoreLanguageNegotiator;

/**
 * Fixed language negotiator.
 *
 * Respects the negotiator weights.
 *
 * @see https://www.drupal.org/project/drupal/issues/2952789
 */
class LanguageNegotiator extends CoreLanguageNegotiator {

  /**
   * {@inheritdoc}
   */
  protected function getEnabledNegotiators($type) {
    $negotiators = parent::getEnabledNegotiators($type);
    asort($negotiators, SORT_NUMERIC);
    return $negotiators;
  }

}
