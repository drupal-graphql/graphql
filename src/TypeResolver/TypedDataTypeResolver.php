<?php

/**
 * @file
 * Contains \Drupal\graphql\TypeResolver\TypedDataTypeResolver.
 */

namespace Drupal\graphql\TypeResolver;

use Drupal\Core\Entity\TypedData\EntityDataDefinitionInterface;
use Drupal\Core\Field\TypedData\FieldItemDataDefinition;
use Drupal\Core\TypedData\ComplexDataDefinitionInterface;
use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\DataReferenceDefinitionInterface;
use Drupal\Core\TypedData\ListDataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\graphql\TypeResolverInterface;
use Drupal\graphql\Utility\String;
use Fubhy\GraphQL\Type\Definition\Types\ListModifier;
use Fubhy\GraphQL\Type\Definition\Types\NonNullModifier;
use Fubhy\GraphQL\Type\Definition\Types\ObjectType;
use Fubhy\GraphQL\Type\Definition\Types\Type;

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
  protected $complexTypes;

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

      $propertyDefinitions = $type->getPropertyDefinitions();
      $propertyKeys = array_keys($propertyDefinitions);
      $propertyNames = String::formatPropertyNameList($propertyKeys);

      $typeName = String::formatTypeName($identifier);
      $typeDescription = $type->getDescription();
      $typeDescription = $typeDescription ? "{$type->getLabel()}: $typeDescription" : $type->getLabel();
      $typeFields = array_reduce($propertyKeys, function ($previous, $key) use ($propertyNames, $propertyDefinitions) {
        $propertyDefinition = $propertyDefinitions[$key];
        if (!$propertyType = $this->typeResolver->resolveRecursive($propertyDefinition)) {
          return $previous;
        }

        $isList = $propertyDefinition->isList();
        $isRequired = $propertyDefinition->isRequired();

        $propertyType = $isList ? new ListModifier($propertyType) : $propertyType;
        $propertyType = $isRequired ? new NonNullModifier($propertyType) : $propertyType;

        if ($propertyDefinition instanceof ComplexDataDefinitionInterface) {
          $resolverFunction = [__CLASS__, 'getPropertyComplexValue'];
        }
        else if ($propertyDefinition instanceof ListDataDefinitionInterface) {
          $resolverFunction = [__CLASS__, 'getPropertyListValue'];
        }
        else if ($propertyDefinition instanceof DataReferenceDefinitionInterface) {
          $resolverFunction = [__CLASS__, 'getPropertyReferenceValue'];
        }
        else if ($propertyDefinition instanceof DataDefinitionInterface) {
          $resolverFunction = [__CLASS__, 'getPropertyPrimitiveValue'];
        }
        else {
          return $previous;
        }

        return $previous + [
          $propertyNames[$key] => [
            'type' => $propertyType,
            'resolve' => $resolverFunction,
            'resolveData' => ['property' => $key],
          ],
        ];
      }, []);

      // Do not register object types without any fields.
      if (empty($typeFields)) {
        return $this->complexTypes[$identifier] = Type::stringType();
      }

      // Statically cache the resolved type based on its data type.
      $this->complexTypes[$identifier] = new ObjectType($typeName, $typeFields, [], NULL, $typeDescription);
      return $this->complexTypes[$identifier];
    };
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
  public static function getPropertyComplexValue(ComplexDataInterface $data, $a, $b, $c, $d, $e, $f, $config) {
    $value = $data->get($config['property']);
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
  public static function getPropertyListValue(ComplexDataInterface $data, $a, $b, $c, $d, $e, $f, $config) {
    /** @var \Drupal\Core\TypedData\ListInterface $value */
    $value = $data->get($config['property']);
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
  public static function getPropertyReferenceValue(ComplexDataInterface $data, $a, $b, $c, $d, $e, $f, $config) {
    /** @var \Drupal\Core\TypedData\DataReferenceInterface $value */
    $value = $data->get($config['property']);
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
  public static function getPropertyPrimitiveValue(ComplexDataInterface $data, $a, $b, $c, $d, $e, $f, $config) {
    $value = $data->get($config['property']);
    return $value->getValue();
  }
}
