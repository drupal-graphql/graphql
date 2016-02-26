<?php

/**
 * @file
 * Contains \Drupal\graphql\TypeResolver\Specialized\LanguageTypeResolver.
 */

namespace Drupal\graphql\TypeResolver\Specialized;

use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\Plugin\DataType\Language;
use Drupal\graphql\TypeResolverInterface;
use Fubhy\GraphQL\Type\Definition\Types\NonNullModifier;
use Fubhy\GraphQL\Type\Definition\Types\ObjectType;
use Fubhy\GraphQL\Type\Definition\Types\Type;

/**
 * Resolves typed data types.
 */
class LanguageTypeResolver implements TypeResolverInterface {
  /**
   * @var \Fubhy\GraphQL\Type\Definition\Types\ObjectType
   */
  protected $languageType;

  /**
   * {@inheritdoc}
   */
  public function applies($type) {
    if ($type instanceof DataDefinitionInterface) {
      return $type->getDataType() === 'language';
    }

    return FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveRecursive($type) {
    if ($type instanceof DataDefinitionInterface && $type->getDataType() === 'language') {
      return $this->getLanguageType();
    }

    return NULL;
  }

  /**
   * @return \Fubhy\GraphQL\Type\Definition\Types\ObjectType
   */
  protected function getLanguageType() {
    if (!isset($this->languageType)) {
      $this->languageType = new ObjectType(
        'Language', [
          'id' => [
            'type' => new NonNullModifier(Type::idType()),
            'resolve' => function ($source) {
              if ($source instanceof Language) {
                return $source->getValue()->getId();
              }
            }
          ],
          'name' => [
            'type' => new NonNullModifier(Type::stringType()),
            'resolve' => function ($source) {
              if ($source instanceof Language) {
                return $source->getValue()->getName();
              }
            }
          ],
          'direction' => [
            'type' => new NonNullModifier(Type::stringType()),
            'resolve' => function ($source) {
              if ($source instanceof Language) {
                return $source->getValue()->getDirection();
              }
            }
          ],
          'weight' => [
            'type' => new NonNullModifier(Type::intType()),
            'resolve' => function ($source) {
              if ($source instanceof Language) {
                return $source->getValue()->getWeight();
              }
            }
          ],
          'locked' => [
            'type' => new NonNullModifier(Type::booleanType()),
            'resolve' => function ($source) {
              if ($source instanceof Language) {
                return $source->getValue()->isLocked();
              }
            }
          ],
        ]
      );
    }

    return $this->languageType;
  }
}
