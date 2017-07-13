<?php

namespace Drupal\graphql_voyager\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\graphql\GraphQL\Utilities\Introspection;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for the GraphQL Voyager visualisation API.
 */
class VoyagerController implements ContainerInjectionInterface {
  use StringTranslationTrait;

  /**
   * The Introspection service.
   *
   * @var \Drupal\graphql\GraphQL\Utilities\Introspection
   */
  protected $introspection;

  /**
   * Constructs a VoyagerController object.
   *
   * @param \Drupal\graphql\GraphQL\Utilities\Introspection $introspection
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
   * Display for the GraphQL Voyager visualisation API.
   *
   * @return array
   *   The render array.
   */
  public function viewExplorer() {
    $introspection_data = $this->introspection->introspect();
    return [
      '#type' => 'page',
      '#theme' => 'page__graphql_voyager',
      '#attached' => [
        'library' => ['graphql_voyager/voyager'],
        'drupalSettings' => [
          'graphQLIntrospectionData' => $introspection_data,
        ],
      ],
    ];
  }

}
