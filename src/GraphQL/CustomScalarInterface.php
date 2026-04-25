<?php

declare(strict_types=1);

namespace Drupal\graphql\GraphQL;

use GraphQL\Language\AST\Node;

/**
 * Interface for custom GraphQL scalar type processing.
 *
 * Implement this interface to define custom scalar types that can be
 * registered in the ResolverRegistry for automatic integration with
 * the schema build process.
 *
 * Any classes implementing custom scalars should be serializable to allow for
 * caching the GraphQL schema.
 *
 * @template InternalT
 */
interface CustomScalarInterface {

  /**
   * Serializes an internal value to include in a response.
   *
   * @param InternalT $value
   *   The internal value representation as returned from a parent field
   *   resolver.
   *
   * @return array|scalar|\JsonSerializable
   *   The representation that can be output in the GraphQL JSON response.
   *
   * @throws \GraphQL\Error\Error
   *   An error in case the value cannot be serialized.
   */
  public function serialize(mixed $value): mixed;

  /**
   * Parses an externally provided value to use as an input.
   *
   * This is a value provided through the variables array next to the query.
   *
   * In the case of an invalid value this method must throw an Exception.
   *
   * @param array|object|scalar $value
   *   The value that was transported in the `variables` array of the GraphQL
   *   query.
   *
   * @return InternalT
   *   The internal value representation for your application.
   *
   * @throws \GraphQL\Error\Error
   *   An error while parsing the value.
   */
  public function parseValue(mixed $value): mixed;

  /**
   * Parses an externally provided literal value to use as an input.
   *
   * A literal value is a value hardcoded in the GraphQL query.
   *
   * In the case of an invalid node or value this method must throw an Exception
   *
   * @param \GraphQL\Language\AST\Node&\GraphQL\Language\AST\ValueNode $valueNode
   *   The GraphQL parsed node that is embedded inside the query string as a
   *   literal.
   * @param array|null $variables
   *   Any variables that are referenced inside the literal.
   *
   * @return InternalT
   *   The internal value representation for your application.
   *
   * @throws \Exception
   *   An exception while parsing the literal value.
   */
  public function parseLiteral(Node $valueNode, ?array $variables = NULL): mixed;

}
