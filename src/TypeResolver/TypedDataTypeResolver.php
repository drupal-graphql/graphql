<?php

/**
 * @file
 * Contains \Drupal\graphql\TypeResolver\TypedDataTypeResolver.
 */

namespace Drupal\graphql\TypeResolver;

use Drupal\Core\Access\AccessibleInterface;
use Drupal\Core\TypedData\ComplexDataDefinitionInterface;
use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\DataReferenceDefinitionInterface;
use Drupal\Core\TypedData\ListDataDefinitionInterface;
use Drupal\graphql\NullType;
use Drupal\graphql\TypeResolverInterface;
use Drupal\graphql\Utility\StringHelper;
use Fubhy\GraphQL\Type\Definition\Types\ListModifier;
use Fubhy\GraphQL\Type\Definition\Types\NonNullModifier;
use Fubhy\GraphQL\Type\Definition\Types\ObjectType;
use Fubhy\GraphQL\Type\Definition\Types\Type;

/**
 * Generically resolves the schema for typed data types.
 */
class TypedDataTypeResolver implements TypeResolverInterface {
  /**
   * The type resolver service.
   *
   * @var \Drupal\graphql\TypeResolverInterface
   */
  protected $typeResolver;

  /**
   * Static cache of resolved complex data types.
   *
   * @var \Fubhy\GraphQL\Type\Definition\Types\TypeInterface[]
   */
  protected $complexTypes = [];

  /**
   * Maps data types to primitive types.
   *
   * @var \Fubhy\GraphQL\Type\Definition\Types\TypeInterface[]
   */
  protected $primitiveMap;

  /**
   * Creates a TypedDataTypeResolver object.
   *
   * @param \Drupal\graphql\TypeResolverInterface $typeResolver
   *   The type resolver service.
   */
  public function __construct(TypeResolverInterface $typeResolver) {
    $this->typeResolver = $typeResolver;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveRecursive($type) {
    if ($type instanceof ListDataDefinitionInterface) {
      return $this->resolveRecursiveList($type);
    }

    if ($type instanceof ComplexDataDefinitionInterface) {
      return $this->resolveRecursiveComplex($type);
    }

    if ($type instanceof DataReferenceDefinitionInterface) {
      return $this->resolveRecursiveReference($type);
    }

    if ($type instanceof DataDefinitionInterface) {
      return $this->resolveRecursivePrimitive($type);
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  public function applies($type) {
    return $type instanceof DataDefinitionInterface;
  }

  /**
   * Resolves list data definitions.
   *
   * @param \Drupal\Core\TypedData\ListDataDefinitionInterface $type
   *   The list data definition to be resolved
   *
   * @return \Fubhy\GraphQL\Type\Definition\Types\TypeInterface|null
   *   The resolved list type or NULL if the item type of the list could not be
   *   resolved.
   */
  protected function resolveRecursiveList(ListDataDefinitionInterface $type) {
    $itemDefinition = $type->getItemDefinition();
    if (!$itemType = $this->typeResolver->resolveRecursive($itemDefinition)) {
      return NULL;
    }

    return $itemType;
  }

  /**
   * Resolves complex data definitions.
   *
   * @param \Drupal\Core\TypedData\ComplexDataDefinitionInterface $type
   *   The complext data definition to be resolved.
   *
   * @return \Fubhy\GraphQL\Type\Definition\Types\ObjectType|null
   *   The object type or NULL if the type does not have any resolvable fields.
   */
  protected function resolveRecursiveComplex(ComplexDataDefinitionInterface $type) {
    if (($identifier = $this->getTypeIdentifier($type)) && array_key_exists($identifier, $this->complexTypes)) {
      return $this->complexTypes[$identifier];
    }

    // Resolve complex data definitions lazily due to recursive definitions.
    return function () use ($type, $identifier) {
      if (array_key_exists($identifier, $this->complexTypes)) {
        return $this->complexTypes[$identifier];
      }

      $typeFields = $this->resolveFields($type);

      // Clean up the field names and remove any empty fields from the list.
      $fieldNames = StringHelper::formatPropertyNameList(array_keys($typeFields));
      $typeFields = array_filter(array_combine($fieldNames, $typeFields));

      if (empty($typeFields)) {
        return $this->complexTypes[$identifier] = new NullType();
      }

      $typeName = StringHelper::formatTypeName($identifier);
      $typeDescription = $type->getDescription();
      $typeDescription = $typeDescription ? "{$type->getLabel()}: $typeDescription" : $type->getLabel();

      // Statically cache the resolved type based on its data type.
      $this->complexTypes[$identifier] = new ObjectType($typeName, $typeFields, [], NULL, $typeDescription);
      return $this->complexTypes[$identifier];
    };
  }

  /**
   * Helper function to resolve the list of available fields for a type.
   *
   * @param \Drupal\Core\TypedData\ComplexDataDefinitionInterface $type
   *   The typed data type to resolve the field list for.
   *
   * @return array
   *   The list of fields for the given type.
   */
  protected function resolveFields(ComplexDataDefinitionInterface $type) {
    $propertyDefinitions = $type->getPropertyDefinitions();
    $propertyKeys = array_keys($propertyDefinitions);

    $typeFields = array_map(function ($propertyKey) use ($propertyDefinitions) {
      $propertyDefinition = $propertyDefinitions[$propertyKey];
      $resolvedProperty = $this->resolveFieldFromProperty($propertyKey, $propertyDefinition);
      return $resolvedProperty;
    }, $propertyKeys);

    return array_combine($propertyKeys, $typeFields);
  }

  /**
   * Helper function to resolve a field definition from a typed data property.
   *
   * @param string $propertyKey
   *   The key of the typed data property.
   * @param \Drupal\Core\TypedData\DataDefinitionInterface $propertyDefinition
   *   The data definition of the property.
   *
   * @return array|null
   *   The resolved field definition.
   */
  protected function resolveFieldFromProperty($propertyKey, DataDefinitionInterface $propertyDefinition) {
    if (!$propertyType = $this->typeResolver->resolveRecursive($propertyDefinition)) {
      return NULL;
    }

    $isList = $propertyDefinition->isList();
    $isRequired = $propertyDefinition->isRequired();

    $propertyType = $isList ? new ListModifier($propertyType) : $propertyType;
    $propertyType = $isRequired ? new NonNullModifier($propertyType) : $propertyType;
    $resolverFunction = $this->getPropertyResolverFunction($propertyDefinition);

    return [
      'type' => $propertyType,
      'resolve' => $resolverFunction,
      'resolveData' => ['property' => $propertyKey],
    ];
  }

  /**
   * Helper function to find the proper resolver function for a given property.
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface $propertyDefinition
   *   The property definition for which to return the resolver function.
   *
   * @return callable|null
   *   The resolver function or NULL if none applies.
   */
  protected function getPropertyResolverFunction(DataDefinitionInterface $propertyDefinition) {
    if ($propertyDefinition instanceof ComplexDataDefinitionInterface) {
      return [__CLASS__, 'getPropertyComplexValue'];
    }

    if ($propertyDefinition instanceof ListDataDefinitionInterface) {
      return [__CLASS__, 'getPropertyListValue'];
    }

    if ($propertyDefinition instanceof DataReferenceDefinitionInterface) {
      return [__CLASS__, 'getPropertyReferenceValue'];
    }

    if ($propertyDefinition instanceof DataDefinitionInterface) {
      return [__CLASS__, 'getPropertyPrimitiveValue'];
    }

    return NULL;
  }

  /**
   * Resolves data reference definitions.
   *
   * @param \Drupal\Core\TypedData\DataReferenceDefinitionInterface $type
   *   The data reference definition to be resolved.
   *
   * @return \Fubhy\GraphQL\Type\Definition\Types\ObjectType|null
   *   The object type or NULL if the type does not have any resolvable fields.
   */
  protected function resolveRecursiveReference(DataReferenceDefinitionInterface $type) {
    $targetDefinition = $type->getTargetDefinition();
    if (!$targetType = $this->typeResolver->resolveRecursive($targetDefinition)) {
      return NULL;
    }

    return $targetType;
  }

  /**
   * Resolves primitive data definitions.
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface $type
   *   The primitive data definition to be resolved.
   *
   * @return \Fubhy\GraphQL\Type\Definition\Types\ObjectType|null
   *   The object type or NULL if the type does not have any resolvable fields.
   */
  protected function resolveRecursivePrimitive(DataDefinitionInterface $type) {
    if (!$resolvedType = $this->getPrimitiveType($type)) {
      return NULL;
    }

    return $resolvedType;
  }

  /**
   * @param \Drupal\Core\TypedData\DataDefinitionInterface|string $type
   *   The data definition for which to return the corresponding primitive type.
   *
   * @return \Fubhy\GraphQL\Type\Definition\Types\TypeInterface|null
   *   The primitive type or NULL if the data type is not a primitive.
   */
  protected function getPrimitiveType(DataDefinitionInterface $type) {
    if (!isset($this->primiviteMap)) {
      $this->primiviteMap = [
        'integer' => Type::intType(),
        'string' => Type::stringType(),
        'boolean' => Type::booleanType(),
        'float' => Type::floatType(),
        'email' => Type::stringType(),
        'timestamp' => Type::intType(),
        'uri' => Type::stringType(),
      ];
    }

    if (($dataType = $this->getTypeIdentifier($type)) && isset($this->primiviteMap[$dataType])) {
      return $this->primiviteMap[$dataType];
    }

    return NULL;
  }

  /**
   * Retrieves the unique type identifier for a specific data type.
   *
   * For basic complex data types, this should be the data type identifier.
   * However, for things like entities and field items, this must be more
   * specific than the data type value returned by the definition object.
   *
   * This is used to generate the schema type name of the complex data type
   * object or interface for this type (e.g. entity:node:article becomes
   * EntityNodeArticle).
   *
   * @param \Drupal\Core\TypedData\DataDefinitionInterface $type
   *   The type definition for which to return the data type identifier.
   *
   * @return string
   *   The data type identifier of the given type definition.
   */
  protected function getTypeIdentifier(DataDefinitionInterface $type) {
    return $type->getDatatype();
  }

  /**
   * Property value resolver callback for complex properties
   *
   * @param \Drupal\Core\TypedData\ComplexDataInterface $data
   *   The parent complex data structure to extract the property from.
   *
   * @return \Drupal\Core\TypedData\ComplexDataInterface|null
   *   The resolved value.
   */
  public static function getPropertyComplexValue(ComplexDataInterface $data = NULL, $a, $b, $c, $d, $e, $f, $config) {
    if (!isset($data)) {
      return NULL;
    }

    $value = $data->get($config['property']);
    if ($value instanceof AccessibleInterface && !$value->access('view')) {
      return NULL;
    }

    return $value;
  }

  /**
   * Property value resolver callback for list properties.
   *
   * @param \Drupal\Core\TypedData\ComplexDataInterface $data
   *   The parent complex data structure to extract the property from.
   *
   * @return array
   *   The resolved value.
   */
  public static function getPropertyListValue(ComplexDataInterface $data = NULL, $a, $b, $c, $d, $e, $f, $config) {
    if (!isset($data)) {
      return NULL;
    }

    /** @var \Drupal\Core\TypedData\ListInterface $value */
    $value = $data->get($config['property']);
    if ($value instanceof AccessibleInterface && !$value->access('view')) {
      return NULL;
    }

    return iterator_to_array($value);
  }

  /**
   * Property value resolver callback for reference properties.
   *
   * @param \Drupal\Core\TypedData\ComplexDataInterface $data
   *   The parent complex data structure to extract the property from.
   *
   * @return \Drupal\Core\TypedData\TypedDataInterface|null
   *   The resolved value.
   */
  public static function getPropertyReferenceValue(ComplexDataInterface $data = NULL, $a, $b, $c, $d, $e, $f, $config) {
    if (!isset($data)) {
      return NULL;
    }

    /** @var \Drupal\Core\TypedData\DataReferenceInterface $value */
    $value = $data->get($config['property']);
    if ($value instanceof AccessibleInterface && !$value->access('view')) {
      return NULL;
    }

    return $value->getTarget();
  }

  /**
   * Property value resolver callback for primitive properties.
   *
   * @param \Drupal\Core\TypedData\ComplexDataInterface $data
   *   The parent complex data structure to extract the property from.
   *
   * @return mixed
   *   The resolved value.
   */
  public static function getPropertyPrimitiveValue(ComplexDataInterface $data = NULL, $a, $b, $c, $d, $e, $f, $config) {
    if (!isset($data)) {
      return NULL;
    }

    $value = $data->get($config['property']);
    if ($value instanceof AccessibleInterface && !$value->access('view')) {
      return NULL;
    }

    return $value->getValue();
  }
}
