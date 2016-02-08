<?php

/**
 * @file
 * Contains \Drupal\graphql\TypeResolver\ComplexDataTypeResolverBase
 */

namespace Drupal\graphql\TypeResolver;

use Drupal\Core\TypedData\ComplexDataDefinitionInterface;
use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\DataReferenceInterface;
use Drupal\Core\TypedData\ListDataDefinitionInterface;
use Drupal\Core\TypedData\ListInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\graphql\NullType;
use Drupal\graphql\TypeResolverInterface;
use Fubhy\GraphQL\Language\Node;
use Fubhy\GraphQL\Type\Definition\Types\Type;

/**
 * Abstract base class for complex data type definition type resolvers.
 */
abstract class ComplexDataTypeResolverBase implements TypeResolverInterface {
  /**
   * The base type resolver service.
   *
   * @var TypeResolverInterface
   */
  protected $typeResolver;

  /**
   * Constructs a ComplexDataTypeResolverBase object.
   *
   * @param TypeResolverInterface $typeResolver
   *   The base type resolver service.
   */
  public function __construct(TypeResolverInterface $typeResolver) {
    $this->typeResolver = $typeResolver;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveRecursive($type) {
    // Prevent infinite loops by resolving complex data definitions lazily.
    if ($type instanceof ComplexDataDefinitionInterface) {
      return function () use ($type) {
        // Optimally, we would also only return NULL here but since that breaks
        // the whole thing we need to invent a null schema type.
        // @todo Revisit this later to try and find a better solution.
        return $this->doResolveRecursive($type) ?: new NullType();
      };
    }

    return NULL;
  }

  /**
   * @param $type
   *
   * @return mixed
   */
  abstract protected function doResolveRecursive($type);

  /**
   * @param ComplexDataDefinitionInterface $definition
   *
   * @return array
   */
  protected function getFieldsFromProperties(ComplexDataDefinitionInterface $definition) {
    return array_filter(array_map(function (DataDefinitionInterface $property) {
      if (!$type = $this->typeResolver->resolveRecursive($property)) {
        return FALSE;
      }

      $args = [];
      if ($property instanceof ListDataDefinitionInterface) {
        $args['offset'] = [
          'type' => Type::intType(),
        ];

        $args['length'] = [
          'type' => Type::intType(),
        ];
      }

      return [
        'type' => $type,
        'args' => $args,
        'resolve' => [__CLASS__, 'resolvePropertyValue'],
      ];
    }, $definition->getPropertyDefinitions()));
  }

  /**
   * @param $string
   *
   * @return string
   */
  protected function stringToName($string) {
    $words = preg_split('/[:\.\-_]/', strtolower($string));
    return implode('', array_map('ucfirst', array_map('trim', $words)));
  }

  /**
   * @param mixed $source
   * @param array $args
   * @param mixed $root
   * @param \Fubhy\GraphQL\Language\Node $field
   *
   * @return \Drupal\Core\TypedData\TypedDataInterface|null
   *
   * @todo Move property resolvers to dedicated tagged services for flexibility.
   */
  public static function resolvePropertyValue($source, array $args = NULL, $root, Node $field) {
    if (!($source instanceof TypedDataInterface)) {
      return NULL;
    }

    $key = $field->get('name')->get('value');
    $value = $source->get($key);
    if ($value instanceof ComplexDataInterface) {
      return $value;
    }

    if ($value instanceof ListInterface) {
      $offset = isset($args['offset']) ? $args['offset'] : 0;
      $length = isset($args['length']) ? $args['length'] : NULL;
      return array_slice(iterator_to_array($value), $offset, $length);
    }

    if ($value instanceof DataReferenceInterface) {
      return $value->getTarget();
    }

    return $value->getValue();
  }
}
