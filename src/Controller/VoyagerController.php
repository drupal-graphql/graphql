<?php

namespace Drupal\graphql\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\graphql\GraphQL\Utility\Introspection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for the GraphQL Voyager visualisation API.
 */
class VoyagerController implements ContainerInjectionInterface {
  /**
   * The introspection service.
   *
   * @var \Drupal\graphql\GraphQL\Utility\Introspection
   */
  protected $introspection;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('graphql.introspection'));
  }

  /**
   * VoyagerController constructor.
   *
   * @param \Drupal\graphql\GraphQL\Utility\Introspection $introspection
   *   The GraphQL introspection service.
   */
  public function __construct(Introspection $introspection) {
    $this->introspection = $introspection;
  }

  /**
   * Display for the GraphQL Voyager visualization API.
   *
   * @param string $schema
   *   The name of the schema to use.
   *
   * @return array The render array.
   *   The render array.
   */
  public function viewVoyager($schema) {
    $introspectionData = $this->introspection->introspect($schema);

    return [
      '#type' => 'page',
      '#theme' => 'page__graphql_voyager',
      '#attached' => [
        'library' => ['graphql/voyager'],
        'drupalSettings' => [
          'graphqlIntrospectionData' => $introspectionData,
        ],
      ],
    ];
  }

}
