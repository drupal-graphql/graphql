<?php

namespace Drupal\graphql\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Language\LanguageManagerInterface;
use Drupal\graphql\SchemaLoader;
use Drupal\graphql\SchemaProviderInterface;
use Fubhy\GraphQL\GraphQL;
use Fubhy\GraphQL\Schema;
use Fubhy\GraphQL\Type\Definition\Types\ObjectType;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Handles GraphQL requests.
 */
class RequestController implements ContainerInjectionInterface {

  /**
   * The GraphQL service.
   *
   * @var \Fubhy\GraphQL\GraphQL
   */
  protected $graphql;

  /**
   * The schema loader service.
   *
   * @var \Drupal\graphql\SchemaLoader
   */
  protected $schemaLoader;

  /**
   * The language manager service.
   *
   * @var \Drupal\Core\Language\LanguageManagerInterface
   */
  protected $languageManager;

  /**
   * Constructs a RequestController object.
   *
   * @param \Fubhy\GraphQL\GraphQL $graphql
   *   The GraphQL service.
   * @param \Drupal\graphql\SchemaLoader $schema_loader
   *   The schema loader service.
   * @param \Drupal\Core\Language\LanguageManagerInterface $language_manager
   *   The language manager service.
   */
  public function __construct(GraphQL $graphql, SchemaLoader $schema_loader, LanguageManagerInterface $language_manager) {
    $this->graphql = $graphql;
    $this->schemaLoader = $schema_loader;
    $this->languageManager = $language_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('graphql.graphql'),
      $container->get('graphql.schema_loader'),
      $container->get('language_manager')
    );
  }

  /**
   * Handles GraphQL requests.
   *
   * @param \Symfony\Component\HttpFoundation\Request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON formatted response.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   */
  public function handleRequest(Request $request) {
    $body = (array) json_decode($request->getContent()) + [
      'query' => NULL,
      'variables' => NULL,
      'operation' => NULL,
    ];

    $query = $request->query->has('query') ? $request->query->get('query') : $body['query'];
    $variables = $request->query->has('variables') ? $request->query->get('variables') : $body['variables'];
    $operation = $request->query->has('operation') ? $request->query->get('operation') : $body['operation'];

    if (empty($query)) {
      throw new NotFoundHttpException();
    }

    $schema = $this->schemaLoader->loadSchema($this->languageManager->getCurrentLanguage());
    $variables = $variables ? (array) json_decode($variables) : NULL;
    $result = $this->graphql->execute($schema, $query, NULL, $variables, $operation);

    $response = new JsonResponse($result);
    return $response->setPrivate();
  }
}
