<?php

namespace Drupal\graphql\GraphQL\Utility;

use Youshido\GraphQL\Schema\AbstractSchema;
use Youshido\GraphQL\Type\InputObject\AbstractInputObjectType;
use Youshido\GraphQL\Type\Object\AbstractObjectType;
use Youshido\GraphQL\Type\TypeInterface;
use Youshido\GraphQL\Type\TypeMap;

class TypeCollector {

  /**
   * Collects all types from the given schema.
   *
   * @param \Youshido\GraphQL\Schema\AbstractSchema $schema
   *   The schema to collect the types from.
   *
   * @return \Youshido\GraphQL\Type\TypeInterface[] $types
   *   An array of types representing all types the schema contains.
   */
  public static function collectTypes(AbstractSchema $schema) {
    $types = [];

    // Collect all types from the query schema.
    static::doCollectTypes($schema->getQueryType(), $types);

    // Collect all types from the mutation schema.
    if ($schema->getMutationType()->hasFields()) {
      static::doCollectTypes($schema->getMutationType(), $types);
    }

    // Collect all types from manually registered types.
    foreach ($schema->getTypesList()->getTypes() as $type) {
      static::doCollectTypes($type, $types);
    }

    return $types;
  }

  /**
   * Recursively collects all types from a given type.
   *
   * @param \Youshido\GraphQL\Type\TypeInterface $type
   *   The type to process.
   * @param \Youshido\GraphQL\Type\TypeInterface[] $types
   *   The type map to write to.
   */
  protected static function doCollectTypes(TypeInterface $type, array &$types = []) {
    if (is_object($type) && array_key_exists($type->getName(), $types)) {
      return;
    }

    switch ($type->getKind()) {
      case TypeMap::KIND_INTERFACE:
      case TypeMap::KIND_UNION:
      case TypeMap::KIND_ENUM:
      case TypeMap::KIND_SCALAR:
        static::insertType($types, $type->getName(), $type);

        if ($type->getKind() == TypeMap::KIND_UNION) {
          /** @var \Youshido\GraphQL\Type\Union\AbstractUnionType $type */
          foreach ($type->getTypes() as $subType) {
            static::doCollectTypes($subType, $types);
          }
        }

        break;

      case TypeMap::KIND_INPUT_OBJECT:
      case TypeMap::KIND_OBJECT:
        /** @var \Youshido\GraphQL\Type\Object\AbstractObjectType $namedType */
        $namedType = $type->getNamedType();
        static::checkAndInsertInterfaces($types, $namedType);

        if (static::insertType($types, $namedType->getName(), $namedType)) {
          static::collectFieldsArgsTypes($types, $namedType);
        }

        break;

      case TypeMap::KIND_LIST:
        static::doCollectTypes($type->getNamedType(), $types);
        break;

      case TypeMap::KIND_NON_NULL:
        static::doCollectTypes($type->getNamedType(), $types);
        break;
    }
  }

  /**
   * Adds all interfaces from a given object to the type list.
   *
   * @param \Youshido\GraphQL\Type\AbstractType[] $types
   *   The type map to write to.
   * @param \Youshido\GraphQL\Type\TypeInterface $type
   *   The type to process.
   */
  protected static function checkAndInsertInterfaces(array &$types, TypeInterface $type) {
    if ($type instanceof AbstractObjectType || $type instanceof AbstractInputObjectType) {
      foreach ((array) $type->getConfig()->getInterfaces() as $interface) {
        static::insertType($types, $interface->getName(), $interface);
      }
    }
  }

  /**
   * Adds all field's arguments from a given type to the type list.
   *
   * @param \Youshido\GraphQL\Type\AbstractType[] $types
   *   The type map to write to.
   * @param \Youshido\GraphQL\Type\TypeInterface $type
   *   The type to process.
   */
  protected static function collectFieldsArgsTypes(array &$types, TypeInterface $type) {
    if ($type instanceof AbstractObjectType || $type instanceof AbstractInputObjectType) {
      foreach ($type->getConfig()->getFields() as $field) {
        $arguments = $field->getConfig()->getArguments();

        if (is_array($arguments)) {
          foreach ($arguments as $argument) {
            static::doCollectTypes($argument->getType(), $types);
          }
        }

        static::doCollectTypes($field->getType(), $types);
      }
    }
  }

  /**
   * Registers a type in the type map.
   *
   * @param \Youshido\GraphQL\Type\AbstractType[] $types
   *   The type map to write to.
   * @param string $name
   *   The name of the type.
   * @param \Youshido\GraphQL\Type\TypeInterface $type
   *   The type to register.
   *
   * @return bool
   *   Whether the type was newly added (TRUE) or not (FALSE). Returns FALSE if
   *   the type already existed on the type map.
   */
  protected static function insertType(array &$types, $name, TypeInterface $type) {
    if (!array_key_exists($name, $types)) {
      $types[$name] = $type;

      return TRUE;
    }

    return FALSE;
  }

}
