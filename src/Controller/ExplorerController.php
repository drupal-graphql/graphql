<?php

namespace Drupal\graphql\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Controller for the GraphiQL query builder IDE.
 */
class ExplorerController implements ContainerInjectionInterface {
  use StringTranslationTrait;

  /**
   * The URL generator service.
   *
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * Constructs a ExplorerController object.
   *
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $url_generator
   *   The url generator service.
   */
  public function __construct(UrlGeneratorInterface $url_generator) {
    $this->urlGenerator = $url_generator;
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
   * Controller for the GraphiQL query builder IDE.
   *
   * @return array
   *   The render array.
   */
  public function viewExplorer() {
    $url = $this->urlGenerator->generate('graphql.request');

    return [
      '#type' => 'page',
      '#theme' => 'page__graphql_explorer',
      '#attached' => [
       'library' => ['graphql/explorer'],
        'drupalSettings' => [
          'graphQLRequestUrl' => $url
        ],
      ],
    ];
  }
}
