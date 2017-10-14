<?php

namespace Drupal\graphql\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Routing\UrlGeneratorInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\graphql\GraphQL\Schema\SchemaLoader;
use Drupal\graphql\GraphQL\Utility\Introspection;
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
   * The introspection service.
   *
   * @var \Drupal\graphql\GraphQL\Utility\Introspection
   */
  protected $introspection;

  /**
   * The schema loader service.
   *
   * @var \Drupal\graphql\GraphQL\Schema\SchemaLoader
   */
  protected $schemaLoader;

  /**
   * Constructs a ExplorerController object.
   *
   * @param \Drupal\Core\Routing\UrlGeneratorInterface $urlGenerator
   *   The url generator service.
   * @param \Drupal\graphql\GraphQL\Utility\Introspection $introspection
   *   The introspection service.
   * @param \Drupal\graphql\GraphQL\Schema\SchemaLoader $schemaLoader
   *   The schema loader service.
   */
  public function __construct(UrlGeneratorInterface $urlGenerator, Introspection $introspection, SchemaLoader $schemaLoader) {
    $this->urlGenerator = $urlGenerator;
    $this->introspection = $introspection;
    $this->schemaLoader = $schemaLoader;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('url_generator'),
      $container->get('graphql.introspection'),
      $container->get('graphql.schema_loader')
    );
  }

  /**
   * Controller for the GraphiQL query builder IDE.
   *
   * @param string $schema
   *   The name of the schema.
   *
   * @return array The render array.
   *   The render array.
   */
  public function viewExplorer($schema) {
    $url = $this->urlGenerator->generate("graphql.query.$schema");
    $introspectionData = $this->introspection->introspect($schema);

    return [
      '#type' => 'page',
      '#theme' => 'page__graphql_explorer',
      '#attached' => [
        'library' => ['graphql/explorer'],
        'drupalSettings' => [
          'graphqlRequestUrl' => $url,
          'graphqlIntrospectionData' => $introspectionData,
        ],
      ],
    ];
  }
}
