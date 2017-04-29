<?php

namespace Drupal\graphql\Controller;

use Drupal\Core\Url;

/**
 * Controller for the GraphiQL configuration page.
 */
class GraphQLConfig {

  /**
   * Displaying the links in GraphQL configuration page.
   *
   * A place to render all GraphQL related links.
   *
   * @return array
   *   Rendered links in admin_block_content theme.
   */
  public function configPageRoot() {
    $links = [
      [
        'title' => 'GraphiQL Explorer',
        'url' => Url::fromRoute('graphql.explorer'),
        'description' => t('An in-browser IDE for exploring GraphQL.'),
        'localized_options' => [],
      ],
      [
        'title' => 'GraphQL Query Maps',
        'url' => Url::fromRoute('entity.graphql_query_map.collection'),
        'description' => t('Configure GraphQL Query Maps.'),
        'localized_options' => [],
      ],
    ];

    return [
      '#theme' => 'admin_block_content',
      '#content' => $links,
    ];
  }
}
