<?php

declare(strict_types=1);

namespace Drupal\graphql\Plugin\LanguageNegotiation;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\language\Attribute\LanguageNegotiation;
use Drupal\language\LanguageNegotiationMethodBase;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class for identifying language based on a graphql query.
 */
#[LanguageNegotiation(
  id: self::METHOD_ID,
  weight: -999,
  name: new TranslatableMarkup("GraphQL operation context"),
  description: new TranslatableMarkup("Determines the language in the context of an operation.")
)]
class OperationLanguageNegotiation extends LanguageNegotiationMethodBase {

  /**
   * The language negotiation method id.
   */
  public const METHOD_ID = 'language-graphql-operation';

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
