<?php

namespace Drupal\graphql;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\graphql\Language\LanguageNegotiator;

/**
 * Workaround for Drupal core bug for language sorting.
 */
class GraphqlServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container): void {
    // Can be removed if this is fixed.
    // https://www.drupal.org/project/drupal/issues/2952789
    if ($container->hasDefinition('language_negotiator')) {
      $container->getDefinition('language_negotiator')
        ->setClass(LanguageNegotiator::class);
    }
  }

}
