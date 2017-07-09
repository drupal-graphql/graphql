<?php

namespace Drupal\graphql_voyager\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for the GraphQL Voyager visualisation API.
 */
class VoyagerController implements ContainerInjectionInterface {
  use StringTranslationTrait;

  /**
   * The URL generator service.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * Constructs a VoyagerController object.
   *
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $urlGenerator
   *   The url generator service.
   */
  public function __construct(UrlGeneratorInterface $urlGenerator) {
    $this->urlGenerator = $urlGenerator;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('url_generator')
    );
  }

  /**
   * Display for the GraphQL Voyager visualisation API.
   *
   * @return array
   *   The render array.
   */
  public function viewExplorer() {
    $url = $this->urlGenerator->generate('graphql.request');

    return [
      '#type' => 'page',
      '#theme' => 'page__graphql_voyager',
      '#attached' => [
        'library' => ['graphql_voyager/voyager'],
        'drupalSettings' => [
          'graphQLRequestUrl' => $url,
        ],
      ],
    ];
  }

}
