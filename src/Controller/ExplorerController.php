<?php

/**
 * @file
 * Contains \Drupal\graphql\Controller\ExplorerController.
 */

namespace Drupal\graphql\Controller;

use Drupal\Component\Utility\Html;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Render\BareHtmlPageRendererInterface;
use Drupal\Core\Render\HtmlResponse;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Response;

/**
 * Acts as intermedgraiate request forwarder for resource plugins.
 */
class ExplorerController implements ContainerInjectionInterface {
  use StringTranslationTrait;

  /**
   * @var \Drupal\Core\Routing\UrlGeneratorInterface
   */
  protected $urlGenerator;

  /**
   * Constructs a ExplorerController object.
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

  public function viewExplorer() {
    $url = $this->urlGenerator->generate('graphql.request');

    return [
      '#type' => 'page',
      '#theme' => 'page__graphql_explorer',
      '#attached' => [
       'library' => ['graphql/explorer'],
        'drupalSettings' => [
          'graphqlRequestUrl' => $url
        ],
      ],
    ];
  }
}
