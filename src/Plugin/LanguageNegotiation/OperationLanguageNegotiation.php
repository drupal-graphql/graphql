<?php

namespace Drupal\graphql\Plugin\LanguageNegotiation;

use Drupal\graphql\GraphQL\Execution\ServerConfig;
use Drupal\language\LanguageNegotiationMethodBase;
use GraphQL\Server\OperationParams;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class for identifying language based on a graphql query.
 *
 * @LanguageNegotiation(
 *   id = Drupal\graphql\Plugin\LanguageNegotiation\OperationLanguageNegotiation::METHOD_ID,
 *   weight = -999,
 *   name = @Translation("GraphQL operation context"),
 *   description = @Translation("Determines the language in the context of an operation.")
 * )
 */
class OperationLanguageNegotiation extends LanguageNegotiationMethodBase {

  /**
   * The language negotiation method id.
   */
  const METHOD_ID = 'language-graphql-operation';

  /**
   * The request stack.
   *
   * @var \Symfony\Component\HttpFoundation\RequestStack
   */
  protected $requestStack;

  /**
   * The current operation.
   *
   * @var \GraphQL\Server\OperationParams
   */
  protected static $operation = NULL;

  /**
   * The current server.
   *
   * @var \Drupal\graphql\GraphQL\Execution\ServerConfig
   */
  protected static $server = NULL;

  /**
   * {@inheritdoc}
   */
  public function getLangcode(Request $request = NULL) {
    if (!empty(static::$operation) && !empty(static::$server)) {
      return static::$server->getPlugin()->getOperationLanguage(static::$operation);
    }

    return NULL;
  }

  /**
   * @param \GraphQL\Server\OperationParams $params
   * @param \Drupal\graphql\GraphQL\Execution\ServerConfig $config
   */
  public static function setContext(OperationParams $params, ServerConfig $config) {
    static::$operation = $params;
    static::$server = $config;
  }

  public static function resetContext() {
    static::$operation = NULL;
    static::$server = NULL;
  }
}