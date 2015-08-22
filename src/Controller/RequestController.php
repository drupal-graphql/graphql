<?php

/**
 * @file
 * Contains \Drupal\graphql\Controller\RequestController.
 */

namespace Drupal\graphql\Controller;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\graphql\SchemaProviderInterface;
use Fubhy\GraphQL\GraphQL;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
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
   * @var \Drupal\graphql\SchemaProviderInterface
   */
  protected $schemaProvider;

  /**
   * Constructs a RequestController object.
   *
   * @param \Fubhy\GraphQL\GraphQL $graphql
   *   The GraphQL service.
   * @param \Drupal\graphql\SchemaProviderInterface $schemaProvider
   */
  public function __construct(GraphQL $graphql, SchemaProviderInterface $schemaProvider) {
    $this->graphql = $graphql;
    $this->schemaProvider = $schemaProvider;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('graphql.graphql'),
      $container->get('graphql.schema')
    );
  }

  /**
   * Handles GraphQL requests.
   *
   * @param \Symfony\Component\HttpFoundation\Request
   *   The request object.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The JSON formatted response.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\NotFoundHttpException
   */
  public function handleRequest(Request $request) {
    $query = $request->query->has('query') ? $request->query->get('query') : $request->request->get('query');
    $variables = $request->query->has('variables') ? $request->query->get('variables') : $request->request->get('variables', []);
    $operation = $request->query->has('operation') ? $request->query->get('operation') : $request->request->get('operation');

    if (empty($query)) {
      throw new NotFoundHttpException();
    }

    $result = $this->graphql->execute($this->schemaProvider->getSchema(), $query, null, $variables, $operation);

    return new Response(json_encode($result), 200, array('Content-Type' => $request->getMimeType('json')));
  }
}
