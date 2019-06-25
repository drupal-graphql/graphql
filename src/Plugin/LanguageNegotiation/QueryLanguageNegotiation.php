<?php

namespace Drupal\graphql\Plugin\LanguageNegotiation;

use Drupal\language\LanguageNegotiationMethodBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class for identifying language based on a graphql query.
 *
 * @LanguageNegotiation(
 *   id = Drupal\graphql\Plugin\LanguageNegotiation\QueryLanguageNegotiation::METHOD_ID,
 *   weight = -999,
 *   name = @Translation("GraphQL query context"),
 *   description = @Translation("Determines the language of a GraphQL query.")
 * )
 */
class QueryLanguageNegotiation extends LanguageNegotiationMethodBase {

  /**
   * The language negotiation method id.
   */
  const METHOD_ID = 'language-graphql-query';

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * {@inheritdoc}
   */
  public function getLangcode(Request $request = NULL) {
    if ($request->attributes->has('_graphql_operation') && $request->attributes->has('_graphql_server')) {
      /** @var \Drupal\graphql\GraphQL\Execution\ServerConfig $server */
      $server = $request->attributes->get('_graphql_server');
      /** @var \GraphQL\Server\OperationParams $operation */
      $operation = $request->attributes->get('_graphql_operation');
      return $server->getPlugin()->getOperationLanguage($operation);
    }

    return NULL;
  }

}