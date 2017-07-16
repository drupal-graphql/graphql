<?php

namespace Drupal\graphql_voyager\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\graphql\Introspection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for the GraphQL Voyager visualisation API.
 */
class VoyagerController implements ContainerInjectionInterface {
  /**
   * The introspection service.
   *
   * @var \Drupal\graphql\Introspection
   */
  protected $introspection;

  /**
   * Constructs a VoyagerController object.
   *
   * @param \Drupal\graphql\Introspection $introspection
   *   The GraphQL introspection service.
   */
  public function __construct(Introspection $introspection) {
    $this->introspection = $introspection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('graphql.introspection')
    );
  }

  /**
   * Display for the GraphQL Voyager visualization API.
   *
   * @return array
   *   The render array.
   */
  public function viewExplorer() {
    $introspectionData = $this->introspection->introspect();

    return [
      '#type' => 'page',
      '#theme' => 'page__graphql_voyager',
      '#attached' => [
        'library' => ['graphql_voyager/voyager'],
        'drupalSettings' => [
          'graphqlIntrospectionData' => $introspectionData,
        ],
      ],
    ];
  }

}
