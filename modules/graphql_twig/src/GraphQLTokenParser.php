<?php

namespace Drupal\graphql_twig;

use Twig_Error_Syntax;
use Twig_Token;

class GraphQLTokenParser extends \Twig_TokenParser {

  public function parse(Twig_Token $token) {
    $stream = $this->parser->getStream();
    if (!$this->parser->isMainScope()) {
      throw new Twig_Error_Syntax('GraphQL queries cannot be defined in blocks.', $token->getLine(), $stream->getSourceContext());
    }

    $stream->expect(Twig_Token::BLOCK_END_TYPE);
    $values = $this->parser->subparse(array($this, 'decideBlockEnd'), true);
    $stream->expect(Twig_Token::BLOCK_END_TYPE);
    if ($values instanceof \Twig_Node_Text) {
      return new GraphQLFragmentNode($values->getAttribute('data'));
    }
  }


  public function decideBlockEnd(Twig_Token $token) {
    return $token->test('endgraphql');
  }

  public function getTag() {
    return 'graphql';
  }

}