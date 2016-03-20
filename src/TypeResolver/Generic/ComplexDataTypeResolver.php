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
use Drupal\graphql\TypeResolverInterface;
use Drupal\graphql\Utility\String;
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
   * @param mixed $type
   *
   * @return \Fubhy\GraphQL\Type\Definition\Types\ObjectType|null
   */
  public function resolveRecursive($type) {
    if (!($type instanceof ComplexDataDefinitionInterface)) {
      return NULL;
    }

    if ($fields = $this->getFieldsFromProperties($type)) {
      return new ObjectType($this->getTypeName($type), $fields);
    }

    return NULL;
  }

  /**
   * @param ComplexDataDefinitionInterface $definition
   *
   * @return array
   */
  protected function getFieldsFromProperties(ComplexDataDefinitionInterface $definition) {
    $properties = $definition->getPropertyDefinitions();

    $keys = array_keys($properties);
    $fields = array_reduce($keys, function ($carry, $key) use ($definition, $properties) {
      $property = $properties[$key];
      $carry[$key] = $this->getFieldFromProperty($definition, $property, $key);
      return $carry;
    }, []);

    $names = String::formatPropertyNameList($keys);
    $fields = array_combine($names, $fields);
    return array_filter($fields);
  }

  /**
   * @param \Drupal\Core\TypedData\ComplexDataDefinitionInterface $definition
   * @param \Drupal\Core\TypedData\DataDefinitionInterface $property
   *
   * @return array
   */
  protected function getFieldFromProperty(ComplexDataDefinitionInterface $definition, DataDefinitionInterface $property, $key) {
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
      'resolveData' => ['key' => $key],
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
  public static function resolveValue($source, array $args = NULL, $root, Node $field, $a, $b, $c, $data) {
    if (!($source instanceof TypedDataInterface)) {
      return NULL;
    }

    $value = $source->get($data['key']);
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
  public static function resolveReferencedValue($source, array $args = NULL, $root, Node $field, $a, $b, $c, $data) {
    if (!($source instanceof TypedDataInterface)) {
      return NULL;
    }

    $value = $source->get($data['key']);
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
  public static function resolveComplexValue($source, array $args = NULL, $root, Node $field, $a, $b, $c, $data) {
    if (!($source instanceof TypedDataInterface)) {
      return NULL;
    }

    $value = $source->get($data['key']);
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
  public static function resolveListValue($source, array $args = NULL, $root, Node $field, $a, $b, $c, $data) {
    if (!($source instanceof TypedDataInterface)) {
      return NULL;
    }

    $value = $source->get($data['key']);
    if (!($value instanceof ListInterface)) {
      return NULL;
    }

    return iterator_to_array($value);
  }
}
