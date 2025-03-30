<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\LanguageNegotiation;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\language\LanguageNegotiationMethodBase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

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
   */
  protected RequestStack $requestStack;

  /**
   * The current execution context.
   */
  protected static ?ResolveContext $context = NULL;

  /**
   * {@inheritdoc}
   */
  public function getLangcode(?Request $request = NULL) {
    if (!empty(static::$context)) {
      return static::$context->getContextLanguage();
    }

    return FALSE;
  }

  /**
   * Set the current resolve context statically which contains the language.
   */
  public static function setContext(?ResolveContext $context = NULL): void {
    static::$context = $context;
  }

}
