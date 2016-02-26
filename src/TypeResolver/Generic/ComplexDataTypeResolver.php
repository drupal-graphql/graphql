<?php

/**
 * @file
 * Contains \Drupal\graphql\TypeResolver\Generic\ComplexDataTypeResolverBase
 */

namespace Drupal\graphql\TypeResolver\Generic;

use Drupal\Core\TypedData\ComplexDataDefinitionInterface;
use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\DataReferenceDefinitionInterface;
use Drupal\Core\TypedData\DataReferenceInterface;
use Drupal\Core\TypedData\ListDataDefinitionInterface;
use Drupal\Core\TypedData\ListInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\graphql\NullType;
use Drupal\graphql\TypeResolverInterface;
use Fubhy\GraphQL\Language\Node;
use Fubhy\GraphQL\Type\Definition\Types\ListModifier;
use Fubhy\GraphQL\Type\Definition\Types\NonNullModifier;
use Fubhy\GraphQL\Type\Definition\Types\ObjectType;

/**
 * Abstract base class for complex data type definition type resolvers.
 */
class ComplexDataTypeResolver implements TypeResolverInterface {
  /**
   * The base type resolver service.
   *
   * @var TypeResolverInterface
   */
  protected $typeResolver;

  /**
   * Constructs a ComplexDataTypeResolverBase object.
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
    return $type instanceof ComplexDataDefinitionInterface;
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
   * @param \Drupal\Core\TypedData\ComplexDataDefinitionInterface $definition
   *
   * @return mixed
   */
  protected function doResolveRecursive(ComplexDataDefinitionInterface $definition) {
    if ($fields = $this->getFieldsFromProperties($definition)) {
      return new ObjectType($this->getTypeName($definition), $fields);
    }

    return NULL;
  }

  /**
   * @param ComplexDataDefinitionInterface $definition
   *
   * @return array
   */
  protected function getFieldsFromProperties(ComplexDataDefinitionInterface $definition) {
    return array_filter(array_map(function (DataDefinitionInterface $property) use ($definition) {
      return $this->getFieldFromProperty($definition, $property);
    }, $definition->getPropertyDefinitions()));
  }

  /**
   * @param \Drupal\Core\TypedData\ComplexDataDefinitionInterface $definition
   * @param \Drupal\Core\TypedData\DataDefinitionInterface $property
   *
   * @return array
   */
  protected function getFieldFromProperty(ComplexDataDefinitionInterface $definition, DataDefinitionInterface $property) {
    if (!$type = $this->typeResolver->resolveRecursive($property)) {
      return FALSE;
    }

    $type = $property->isList() ? new ListModifier($type) : $type;
    $type = $property->isRequired() ? new NonNullModifier($type) : $type;

    if ($property instanceof ComplexDataDefinitionInterface) {
      $resolve = [__CLASS__, 'resolveComplexValue'];
    }
    else if ($property instanceof ListDataDefinitionInterface) {
      $resolve = [__CLASS__, 'resolveListValue'];
    }
    else if ($property instanceof DataReferenceDefinitionInterface) {
      $resolve = [__CLASS__, 'resolveReferencedValue'];
    }
    else {
      $resolve = [__CLASS__, 'resolveValue'];
    }

    return [
      'type' => $type,
      'resolve' => $resolve,
    ];
  }

  /**
   * @param \Drupal\Core\TypedData\ComplexDataDefinitionInterface $definition
   *
   * @return string
   */
  protected function getTypeIdentifier(ComplexDataDefinitionInterface $definition) {
    return $definition->getDataType();
  }

  /**
   * @param \Drupal\Core\TypedData\ComplexDataDefinitionInterface $definition
   *
   * @return string
   */
  protected function getTypeName(ComplexDataDefinitionInterface $definition) {
    $identifier = $this->getTypeIdentifier($definition);
    $words = preg_split('/[:\.\-_]/', strtolower($identifier));
    return implode('', array_map('ucfirst', array_map('trim', $words)));
  }

  /**
   * @param mixed $source
   * @param array $args
   * @param mixed $root
   * @param \Fubhy\GraphQL\Language\Node $field
   *
   * @return \Drupal\Core\TypedData\TypedDataInterface|null
   */
  public static function resolveValue($source, array $args = NULL, $root, Node $field) {
    if (!($source instanceof TypedDataInterface)) {
      return NULL;
    }

    $key = $field->get('name')->get('value');
    $value = $source->get($key);
    return $value->getValue();
  }

  /**
   * @param mixed $source
   * @param array $args
   * @param mixed $root
   * @param \Fubhy\GraphQL\Language\Node $field
   *
   * @return \Drupal\Core\TypedData\TypedDataInterface|null
   */
  public static function resolveReferencedValue($source, array $args = NULL, $root, Node $field) {
    if (!($source instanceof TypedDataInterface)) {
      return NULL;
    }

    $key = $field->get('name')->get('value');
    $value = $source->get($key);
    if (!($value instanceof DataReferenceInterface)) {
      return NULL;
    }

    return $value->getTarget();
  }

  /**
   * @param mixed $source
   * @param array $args
   * @param mixed $root
   * @param \Fubhy\GraphQL\Language\Node $field
   *
   * @return \Drupal\Core\TypedData\TypedDataInterface|null
   */
  public static function resolveComplexValue($source, array $args = NULL, $root, Node $field) {
    if (!($source instanceof TypedDataInterface)) {
      return NULL;
    }

    $key = $field->get('name')->get('value');
    $value = $source->get($key);
    if (!($value instanceof ComplexDataInterface)) {
      return NULL;
    }

    return $value;
  }

  /**
   * @param mixed $source
   * @param array $args
   * @param mixed $root
   * @param \Fubhy\GraphQL\Language\Node $field
   *
   * @return \Drupal\Core\TypedData\TypedDataInterface|null
   */
  public static function resolveListValue($source, array $args = NULL, $root, Node $field) {
    if (!($source instanceof TypedDataInterface)) {
      return NULL;
    }

    $key = $field->get('name')->get('value');
    $value = $source->get($key);
    if (!($value instanceof ListInterface)) {
      return NULL;
    }

    return iterator_to_array($value);
  }
}
