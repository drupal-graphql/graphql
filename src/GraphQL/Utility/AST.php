<?php

declare(strict_types=1);

namespace Drupal\graphql\GraphQL\Utility;

use GraphQL\Language\AST\DocumentNode;
use GraphQL\Language\AST\NodeList;
use GraphQL\Utils\AST as ASTBase;

/**
 * Forward port of GraphQL 15 function in AST class.
 *
 * @todo Remove when upgrading to GraphQL v15.
 * @internal
 */
class AST extends ASTBase {

  /**
   * Provided a collection of ASTs, presumably each from different files,
   * concatenate the ASTs together into batched AST, useful for validating many
   * GraphQL source files which together represent one conceptual application.
   *
   * @param array<\GraphQL\Language\AST\DocumentNode> $documents
   *
   * @api
   */
  public static function concatAST(array $documents): DocumentNode {
    /** @var array<int, \GraphQL\Language\AST\Node&\GraphQL\Language\AST\DefinitionNode> $definitions */
    $definitions = [];
    foreach ($documents as $document) {
      foreach ($document->definitions as $definition) {
        $definitions[] = $definition;
      }
    }

    return new DocumentNode(['definitions' => new NodeList($definitions)]);
  }

}
