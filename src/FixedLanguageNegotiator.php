<?php

namespace Drupal\graphql;

use Drupal\language\LanguageNegotiator;

/**
 * Fixed language negotiator.
 *
 * Respects the negotiator weights.
 *
 * @see https://www.drupal.org/project/drupal/issues/2952789
 */
class FixedLanguageNegotiator extends LanguageNegotiator {

  /**
   * {@inheritdoc}
   */
  protected function getEnabledNegotiators($type) {
    $negotiators = parent::getEnabledNegotiators($type);
    asort($negotiators, SORT_NUMERIC);
    return $negotiators;
  }

}
