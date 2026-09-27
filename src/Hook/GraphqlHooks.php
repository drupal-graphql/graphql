<?php

declare(strict_types=1);

namespace Drupal\graphql\Hook;

use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Hook implementations for graphql.
 */
class GraphqlHooks {
  use StringTranslationTrait;

  /**
   * Implements hook_help().
   */
  #[Hook('help')]
  public function help(string $routeName): ?string {
    if ($routeName !== 'help.page.graphql') {
      return NULL;
    }
    $title = $this->t('About');
    // @mago-expect lint:drupal/translatable-string
    $description = $this->t('
<p>The GraphQL module lets you build a <a href="https://graphql.org/" target="_blank">GraphQL</a>
  API for Drupal using a custom schema. Client applications can query the API to
  request exactly the data they need.</p>
<p>Developers define the schema and connect its fields to Drupal data and other
  sources using resolvers and reusable data producers. Administrators can configure
  API endpoints, test queries in the GraphQL explorer, and browse the schema.</p>');
    return <<<EOT
        <h3>{$title}</h3>
        {$description}
        EOT;
  }

}
