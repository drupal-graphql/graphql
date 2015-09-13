<?php

/**
 * @file
 * Contains \Drupal\graphql\TypeResolver\DataReferenceTypeResolver.
 */

namespace Drupal\graphql\TypeResolver;

use Drupal\Core\TypedData\DataReferenceDefinitionInterface;
use Drupal\graphql\TypeResolverInterface;
use Fubhy\GraphQL\Language\Node;
use Fubhy\GraphQL\Type\Definition\Types\NonNullModifier;

/**
 * Resolves typed data types.
 */
class DataReferenceTypeResolver implements TypeResolverInterface {
  /**
   * The base type resolver service.
   *
   * @var TypeResolverInterface
   */
  protected $resolver;

  /**
   * Constructs a DataReferenceTypeResolver object.
   *
   * @param TypeResolverInterface $resolver
   *   The base type resolver service.
   */
  public function __construct(TypeResolverInterface $resolver) {
    $this->resolver = $resolver;
  }

  /**
   * @param mixed $type
   *
   * @return bool
   */
  public function applies($type) {
    return $type instanceof DataReferenceDefinitionInterface;
  }

  /**
   * @param mixed $type
   *
   * @return \Fubhy\GraphQL\Type\Definition\Types\TypeInterface|callable|null
   */
  public function resolveRecursive($type) {
    if ($type instanceof DataReferenceDefinitionInterface) {
      $target = $type->getTargetDefinition();
      if ($resolved = $this->resolver->resolveRecursive($target)) {
        return $type->isRequired() ? new NonNullModifier($resolved) : $resolved;
      }
    }

    return NULL;
  }
}
