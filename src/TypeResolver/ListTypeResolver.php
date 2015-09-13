<?php

/**
 * @file
 * Contains \Drupal\graphql\TypeResolver\ListTypeResolver.
 */

namespace Drupal\graphql\TypeResolver;

use Drupal\Core\TypedData\ListDataDefinitionInterface;
use Drupal\graphql\TypeResolverInterface;
use Fubhy\GraphQL\Type\Definition\Types\ListModifier;
use Fubhy\GraphQL\Type\Definition\Types\NonNullModifier;

/**
 * Resolves typed data types.
 */
class ListTypeResolver implements TypeResolverInterface {
  /**
   * The base type resolver service.
   *
   * @var TypeResolverInterface
   */
  protected $resolver;

  /**
   * Constructs a ListTypeResolver object.
   *
   * @param TypeResolverInterface $resolver
   *   The base type resolver service.
   */
  public function __construct(TypeResolverInterface $resolver) {
    $this->resolver = $resolver;
  }

  /**
   * {@inheritdoc}
   */
  public function applies($type) {
    return $type instanceof ListDataDefinitionInterface;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveRecursive($type) {
    if ($type instanceof ListDataDefinitionInterface) {
      if ($resolved = $this->resolver->resolveRecursive($type->getItemDefinition())) {
        $resolved = new ListModifier($resolved);
        return $type->isRequired() ? new NonNullModifier($resolved) : $resolved;
      }
    }

    return NULL;
  }
}
