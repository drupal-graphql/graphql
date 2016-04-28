<?php

/**
 * @file
 * Contains \Drupal\graphql\TypeResolver\ContentEntityTypeResolver.
 */

namespace Drupal\graphql\TypeResolver;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\Plugin\DataType\EntityAdapter;
use Drupal\Core\Entity\TypedData\EntityDataDefinition;
use Drupal\Core\Entity\TypedData\EntityDataDefinitionInterface;
use Drupal\Core\Field\TypedData\FieldItemDataDefinition;
use Drupal\Core\TypedData\ComplexDataDefinitionInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\graphql\TypeResolverInterface;
use Drupal\graphql\Utility\String;
use Fubhy\GraphQL\Language\Node;
use Fubhy\GraphQL\Type\Definition\Types\InterfaceType;
use Fubhy\GraphQL\Type\Definition\Types\ObjectType;

/**
 * Resolves typed data types.
 */
class ContentEntityTypeResolver implements TypeResolverInterface {
  /**
   * The type resolver service.
   *
   * @var \Drupal\graphql\TypeResolverInterface
   */
  protected $typeResolver;
  
  /**
   * The typed data manager service.
   *
   * @var \Drupal\Core\TypedData\TypedDataManager
   */
  protected $typedDataManager;

  /**
   * The entity manager service.
   *
   * @var \Drupal\Core\Entity\EntityManagerInterface
   */
  protected $entityManager;

  /**
   * Static cache of resolved types.
   *
   * @var \Fubhy\GraphQL\Type\Definition\Types\TypeInterface[]
   */
  protected $cachedTypes;

  /**
   * Constructs a ContentEntityTypeResolver object.
   *
   * @param TypeResolverInterface $typeResolver
   *   The base type resolver service.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entityManager
   *   The entity manager service.
   * @param \Drupal\Core\TypedData\TypedDataManagerInterface $typedDataManager
   *   The typed data manager service.
   */
  public function __construct(TypeResolverInterface $typeResolver, EntityManagerInterface $entityManager, TypedDataManagerInterface $typedDataManager) {
    $this->typeResolver = $typeResolver;
    $this->entityManager = $entityManager;
    $this->typedDataManager = $typedDataManager;
  }

  /**
   * {@inheritdoc}
   */
  public function applies($type) {
    return $type instanceof EntityDataDefinitionInterface;
  }

  /**
   * {@inheritdoc}
   */
  public function resolveRecursive($type) {
    /** @var \Drupal\Core\Entity\TypedData\EntityDataDefinitionInterface $type */
    $entityTypeId = $type->getEntityTypeId();
    $bundleInfo = $this->entityManager->getBundleInfo($entityTypeId);
    $bundleKeys = array_keys($bundleInfo);

    // The bundles available for this entity type.
    $availableBundles = array_diff($bundleKeys, [$entityTypeId]);

    // The bundles defined as constraint on the the type definition.
    $constraintBundles = array_diff($bundleKeys, $type->getBundles());
    $constraintBundles = array_intersect($constraintBundles, $availableBundles);

    // We currently do not support multiple bundle constraints although we could
    // potentially support that in the future through union types.
    $constraintBundle = count($constraintBundles) === 1 ? reset($constraintBundles) : NULL;

    // Check if we've already built the type definitions for this entity type.
    $cacheIdentifier = isset($constraintBundle) ? "$entityTypeId:$constraintBundle" : $entityTypeId;
    if (array_key_exists($entityTypeId, $this->cachedTypes)) {
      return $this->cachedTypes[$cacheIdentifier];
    }

    // Resolve complex data definitions lazily due to recursive definitions.
    return function () use ($entityTypeId, $availableBundles, $constraintBundle, $cacheIdentifier) {
      if (array_key_exists($entityTypeId, $this->cachedTypes)) {
        return $this->cachedTypes[$cacheIdentifier];
      }

      // Name of the interface- or the object type (if there are no bundles).
      $entityTypeName = String::formatTypeName("entity:$entityTypeId");

      // Get all shared properties for all bundles and the interface.
      $baseFields = $this->resolveBaseFields($entityTypeId);
      
      // If there are no bundles, simply handle the entity type as a
      // stand-alone object type.
      if (empty($availableBundles)) {
        $this->cachedTypes[$entityTypeId] = new ObjectType($entityTypeName, $baseFields);
        return $this->cachedTypes[$entityTypeId];
      }

      // Else, create an interface type definition and the object type definiton
      // for all available bundles.
      $objectTypeResolver = [__CLASS__, 'resolveObjectType'];
      $this->cachedTypes[$entityTypeId] = new InterfaceType($entityTypeName, $baseFields, $objectTypeResolver);

      foreach ($availableBundles as $bundleKey) {
        $bundleName = String::formatTypeName("entity:$entityTypeId:$bundleKey");
        $bundleFields = $this->resolveBundleFields($entityTypeId, $bundleKey);
        $typeInterfaces = [$this->cachedTypes[$entityTypeId]];
        $this->cachedTypes["$entityTypeId:$bundleKey"] = new ObjectType($bundleName, $bundleFields + $baseFields, $typeInterfaces);
      }

      return $this->cachedTypes[$cacheIdentifier];
//
//      $propertyDefinitions = $type->getPropertyDefinitions();
//      $propertyKeys = array_keys($propertyDefinitions);
//      $propertyNames = String::formatPropertyNameList($propertyKeys);
//
//      $typeName = String::formatTypeName($identifier);
//      $typeDescription = $type->getDescription();
//      $typeDescription = $typeDescription ? "{$type->getLabel()}: $typeDescription" : $type->getLabel();
//      $typeFields = array_reduce($propertyKeys, function ($previous, $key) use ($propertyNames, $propertyDefinitions) {
//        $propertyDefinition = $propertyDefinitions[$key];
//        if (!$propertyType = $this->typeResolver->resolveRecursive($propertyDefinition)) {
//          return $previous;
//        }
//
//        $isList = $propertyDefinition->isList();
//        $isRequired = $propertyDefinition->isRequired();
//
//        $propertyType = $isList ? new ListModifier($propertyType) : $propertyType;
//        $propertyType = $isRequired ? new NonNullModifier($propertyType) : $propertyType;
//
//        if ($propertyDefinition instanceof ComplexDataDefinitionInterface) {
//          $resolverFunction = [__CLASS__, 'getPropertyComplexValue'];
//        }
//        else if ($propertyDefinition instanceof ListDataDefinitionInterface) {
//          $resolverFunction = [__CLASS__, 'getPropertyListValue'];
//        }
//        else if ($propertyDefinition instanceof DataReferenceDefinitionInterface) {
//          $resolverFunction = [__CLASS__, 'getPropertyReferenceValue'];
//        }
//        else if ($propertyDefinition instanceof DataDefinitionInterface) {
//          $resolverFunction = [__CLASS__, 'getPropertyPrimitiveValue'];
//        }
//        else {
//          return $previous;
//        }
//
//        return $previous + [
//          $propertyNames[$key] => [
//            'type' => $propertyType,
//            'resolve' => $resolverFunction,
//            'resolveData' => ['property' => $key],
//          ],
//        ];
//      }, []);
//
//      // Do not register object types without any fields.
//      if (empty($typeFields)) {
//        return $this->complexTypes[$identifier] = Type::stringType();
//      }
//
//      // Statically cache the resolved type based on its data type.
//      $this->complexTypes[$identifier] = new ObjectType($typeName, $typeFields, [], NULL, $typeDescription);
//      return $this->complexTypes[$identifier];
    };
  }

  protected function resolveBaseFields($entityTypeId) {
    $definition = $this->typedDataManager->createDataDefinition("entity:$entityTypeId");
    $propertyDefinitions = $definition->getPropertyDefinitions();
    $propertyKeys = array_keys($propertyDefinitions);
    $propertyNames = String::formatPropertyNameList($propertyKeys);

    $typeFields = array_map(function ($propertyKey) use ($propertyDefinitions) {
      $propertyDefinition = $propertyDefinitions[$propertyKey];
      return $this->resolveFieldFromProperty($propertyKey, $propertyDefinition);
    }, $propertyKeys);

    $typeFields = array_filter(array_combine($propertyNames, $typeFields));

    return $typeFields;
  }

  protected function resolveBundleFields($entityTypeId, $bundleKey) {
    /** @var \Drupal\Core\Entity\TypedData\EntityDataDefinitionInterface $definition */
    $definition = $this->typedDataManager->createDataDefinition("entity:$entityTypeId:$bundleKey");
    $propertyDefinitions = $definition->getPropertyDefinitions();
    $propertyKeys = array_keys($propertyDefinitions);
    $propertyNames = String::formatPropertyNameList($propertyKeys);

    $typeFields = array_map(function ($propertyKey) use ($propertyDefinitions) {
      $propertyDefinition = $propertyDefinitions[$propertyKey];
      return $this->resolveFieldFromProperty($propertyKey, $propertyDefinition);
    }, $propertyKeys);

    $typeFields = array_filter(array_combine($propertyNames, $typeFields));

    return $typeFields;
  }

  protected function resolveFieldFromProperty($propertyKey, $propertyDefinition) {
    if (!$propertyType = $this->typeResolver->resolveRecursive($propertyDefinition)) {
      return NULL;
    }

    return [
      'type' => $propertyType,
      'resolve' => [__CLASS__, 'getPropertyValue'],
      'resolveData' => ['propertyKey' => $propertyKey],
    ];
  }

  public static function getPropertyValue() {
    return 'asd';
  }

  public static function resolveObjectType($source) {
    if (!($source instanceof EntityAdapter) || !$entity = $source->getValue()) {
      return NULL;
    }

    $currentLanguage = \Drupal::service('language_manager')->getCurrentLanguage();
    $loadedSchema = \Drupal::service('graphql.schema_loader')->loadSchema($currentLanguage);
    $typeMap = $loadedSchema->getTypeMap();

    $entityTypeId = $entity->getEntityType()->id();
    $bundleKey = $entity->bundle();
    $typeIdentifier = 'entity:' . (($bundleKey !== $entityTypeId) ? "$entityTypeId:$bundleKey" : $entityTypeId);
    $typeName = String::formatTypeName($typeIdentifier);
    
    return isset($typeMap[$typeName]) ? $typeMap[$typeName] : NULL;
  }
}
