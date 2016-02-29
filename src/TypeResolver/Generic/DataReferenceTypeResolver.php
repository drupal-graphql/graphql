<?php

/**
 * @file
 * Contains \Drupal\graphql\TypeResolver\Generic\DataReferenceTypeResolver.
 */

namespace Drupal\graphql\TypeResolver\Generic;

use Drupal\Core\TypedData\DataReferenceDefinitionInterface;
use Drupal\graphql\TypeResolverInterface;
use Fubhy\GraphQL\Language\Node;

/**
 * Resolves typed data types.
 */
class DataReferenceTypeResolver implements TypeResolverInterface {
  /**
   * The base type resolver service.
   *
   * @var TypeResolverInterface
   */
  protected $typeResolver;

  /**
   * Constructs a DataReferenceTypeResolver object.
   *
   * @param TypeResolverInterface $type_resolver
   *   The base type resolver service.
   */
  public function __construct(TypeResolverInterface $type_resolver) {
    $this->typeResolver = $type_resolver;
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
   * {@inheritdoc}
   */
  public function resolveRecursive($type) {
    if ($type instanceof DataReferenceDefinitionInterface) {
      $target = $type->getTargetDefinition();
      if ($resolved = $this->typeResolver->resolveRecursive($target)) {
        return $resolved;
      }
    }

    return NULL;
  }
}
