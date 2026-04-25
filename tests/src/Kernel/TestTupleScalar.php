<?php

declare(strict_types=1);

namespace Drupal\Tests\graphql\Kernel;

use Drupal\graphql\GraphQL\CustomScalarInterface;
use GraphQL\Error\UserError;
use GraphQL\Language\AST\Node;
use GraphQL\Type\Definition\NonNull;
use GraphQL\Type\Definition\StringType;
use GraphQL\Utils\AST;

/**
 * A scalar that (de)serializes a tuple for our tests.
 *
 * @phpstan-implements CustomScalarInterface<array{string, string}>
 */
class TestTupleScalar implements CustomScalarInterface {

  /**
   * {@inheritdoc}
   */
  public function serialize(mixed $value): mixed {
    return implode(":", $value);
  }

  /**
   * {@inheritdoc}
   */
  public function parseValue(mixed $value): mixed {
    if (!is_string($value)) {
      throw new UserError('TestTuple should be a colon (:) separated string.');
    }

    return explode(':', $value);
  }

  /**
   * {@inheritdoc}
   */
  public function parseLiteral(Node $valueNode, ?array $variables = NULL): mixed {
    $value = AST::valueFromAST($valueNode, new NonNull(new StringType()), $variables);
    if (!is_string($value)) {
      throw new UserError('TestTuple should be a colon (:) separated string.');
    }

    return explode(':', $value);
  }

}
