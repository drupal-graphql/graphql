<?php

/**
 * @file
 * Contains \Drupal\graphql\TypeResolver\PrimitiveTypeResolver.
 */

namespace Drupal\graphql\TypeResolver;

use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\graphql\TypeResolverInterface;
use Fubhy\GraphQL\Type\Definition\Types\NonNullModifier;
use Fubhy\GraphQL\Type\Definition\Types\Type;

/**
 * Resolves typed data types.
 */
class PrimitiveTypeResolver implements TypeResolverInterface {
  /**
   * {@inheritdoc}
   */
  public function applies($type) {
    if ($type instanceof DataDefinitionInterface) {
      return (bool) $this->getType($type->getDataType());
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveRecursive($type) {
    if ($type instanceof DataDefinitionInterface) {
      if ($resolved = $this->getType($type->getDataType()) ?: NULL) {
        return $type->isRequired() ? new NonNullModifier($resolved) : $resolved;
      }
    }

    return NULL;
  }

  /**
   * @param string $type
   *
   * @return null
   */
  protected function getType($type) {
    if (!isset($this->typeMap)) {
      $this->typeMap = [
        'integer' => Type::intType(),
        'string' => Type::stringType(),
        'boolean' => Type::booleanType(),
        'float' => Type::floatType(),
      ];

      // @todo The following types are not actually primitives and there is
      // potential for implementing some additional schema and logic for them.
      $this->typeMap += [
        'email' => Type::stringType(),
        'timestamp' => Type::intType(),
        'uri' => Type::stringType(),
      ];
    }

    return isset($this->typeMap[$type]) ? $this->typeMap[$type] : NULL;
  }
}
