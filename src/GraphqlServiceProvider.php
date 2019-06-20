<?php

namespace Drupal\graphql;

use Drupal\Core\DependencyInjection\ContainerBuilder;
use Drupal\Core\DependencyInjection\ServiceProviderBase;
use Drupal\graphql\GraphQL\Context\ContextRepository;
use Drupal\graphql\Language\FixedLanguageNegotiator;

class GraphqlServiceProvider extends ServiceProviderBase {

  /**
   * {@inheritdoc}
   */
  public function alter(ContainerBuilder $container) {
    // Replace the context repository with a stack based one so we can
    // re-evaluate contexts at query time.
    $container
      ->getDefinition('context.repository')
      ->setClass(ContextRepository::class);
  }

}
