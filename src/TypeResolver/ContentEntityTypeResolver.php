<?php

/**
 * @file
 * Contains \Drupal\graphql\TypeResolver\ContentEntityTypeResolver.
 */

namespace Drupal\graphql\TypeResolver;

use Drupal\Core\Access\AccessibleInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\Plugin\DataType\EntityAdapter;
use Drupal\Core\Entity\TypedData\EntityDataDefinitionInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\TypedData\ComplexDataDefinitionInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\graphql\NullType;
use Drupal\graphql\TypeResolverInterface;
use Drupal\graphql\Utility\String;
use Fubhy\GraphQL\Language\Node;
use Fubhy\GraphQL\Type\Definition\Types\EnumType;
use Fubhy\GraphQL\Type\Definition\Types\InterfaceType;
use Fubhy\GraphQL\Type\Definition\Types\ListModifier;
use Fubhy\GraphQL\Type\Definition\Types\NonNullModifier;
use Fubhy\GraphQL\Type\Definition\Types\ObjectType;
use Fubhy\GraphQL\Type\Definition\Types\Type;

/**
 * Resolves the schema for content entities.
 */
class ContentEntityTypeResolver extends TypedDataTypeResolver {
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
  protected $cachedTypes = [];

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
    parent::__construct($typeResolver);
    $this->entityManager = $entityManager;
    $this->typedDataManager = $typedDataManager;
  }

  /**
   * {@inheritdoc}
   */
  public function applies($type) {
    if ($type instanceof EntityDataDefinitionInterface) {
      $entityTypeId = $type->getEntityTypeId();
      $entityType = $this->entityManager->getDefinition($entityTypeId);

      return $entityType->isSubclassOf('\Drupal\Core\Entity\ContentEntityInterface');
    }

    return FALSE;
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
    $constraintBundles = $type->getBundles();
    $constraintBundles = $constraintBundles ? array_diff($bundleKeys, $constraintBundles) : [];
    $constraintBundles = array_intersect($constraintBundles, $availableBundles);

    // We currently do not support multiple bundle constraints although we could
    // potentially support that in the future through union types.
    $constraintBundle = count($constraintBundles) === 1 ? reset($constraintBundles) : NULL;

    // Check if we've already built the type definitions for this entity type.
    $cacheKey = isset($constraintBundle) ? $constraintBundle : $entityTypeId;
    if (array_key_exists($entityTypeId, $this->cachedTypes)) {
      return $this->cachedTypes[$entityTypeId][$cacheKey];
    }

    // Resolve complex data definitions lazily due to recursive definitions.
    return function () use ($entityTypeId, $cacheKey) {
      if (array_key_exists($entityTypeId, $this->cachedTypes)) {
        return $this->cachedTypes[$entityTypeId][$cacheKey];
      }

      // Initialize the static cache for this entity type.
      $staticCache = &$this->cachedTypes[$entityTypeId];
      $staticCache = [];

      // Retrieve the field map for the entity type (contains base and bundle
      // specific fields).
      $fieldMap = $this->getEntityTypeFieldMap($entityTypeId);
      $baseFields = $fieldMap['base'];

      $entityTypeName = String::formatTypeName("entity:$entityTypeId");
      if (!empty($fieldMap['bundles'])) {
        // If there are bundles, create an interface type definition and the
        // object type definition for all available bundles.
        $objectTypeResolver = [__CLASS__, 'getObjectTypeFromData'];
        $staticCache[$entityTypeId] = new InterfaceType($entityTypeName, $baseFields, $objectTypeResolver);
        $typeInterfaces = [$staticCache[$entityTypeId]];

        foreach ($fieldMap['bundles'] as $bundleKey => $bundleFields) {
          $bundleName = String::formatTypeName("entity:$entityTypeId:$bundleKey");
          $staticCache[$bundleKey] = new ObjectType($bundleName, $bundleFields + $baseFields, $typeInterfaces);
        }
      }
      else {
        // If there are no bundles, simply handle the entity type as a
        // stand-alone object type.
        $staticCache[$entityTypeId] = !empty($baseFields) ? new ObjectType($entityTypeName, $baseFields) : new NullType();
      }

      return $staticCache[$cacheKey];
    };
  }

  /**
   * Helper function to retrieve the field schema definitions for an entity.
   *
   * Retrieves the field schema definitions for the base properties and the
   * bundle specific properties for each available bundle.
   *
   * @param string $entityTypeId
   *   The entity type for which to build the field schema definitions.
   *
   * @return array
   *   A structured array containing the field schema definitions for the base-
   *   and bundle specific properties.
   */
  protected function getEntityTypeFieldMap($entityTypeId) {
    /** @var \Drupal\Core\Entity\TypedData\EntityDataDefinitionInterface $baseDefinition */
    $baseDefinition = $this->typedDataManager->createDataDefinition("entity:$entityTypeId");

    // Resolve fields from base properties.
    $baseFields = $this->resolveFields($baseDefinition);
    $baseFieldNames = String::formatPropertyNameList(array_keys($baseFields));

    // Store the resolved base fields in the field map.
    $fieldMap['base'] = array_filter(array_combine($baseFieldNames, $baseFields));

    // The bundles available for this entity type.
    $bundleInfo = $this->entityManager->getBundleInfo($entityTypeId);
    $bundleKeys = array_keys($bundleInfo);
    $availableBundles = array_diff($bundleKeys, [$entityTypeId]);

    foreach ($availableBundles as $bundleKey) {
      /** @var \Drupal\Core\Entity\TypedData\EntityDataDefinitionInterface $bundleDefinition */
      $bundleDefinition = $this->typedDataManager->createDataDefinition("entity:$entityTypeId:$bundleKey");
      $bundleFields = $this->resolveFields($bundleDefinition);
      $bundleFields = array_diff_key($bundleFields, $baseFields);
      $bundleFieldNames = String::formatPropertyNameList(array_keys($bundleFields), $baseFieldNames);

      // Store the resolved bundle fields in the field map.
      $fieldMap['bundles'][$bundleKey] = array_filter(array_combine($bundleFieldNames, $bundleFields));
    }

    return $fieldMap;
  }

  /**
   * {@inheritdoc}
   */
  protected function resolveFields(ComplexDataDefinitionInterface $type) {
    /** @var \Drupal\Core\Entity\TypedData\EntityDataDefinitionInterface $type */
    $propertyDefinitions = $type->getPropertyDefinitions();
    $entityTypeId = $type->getEntityTypeId();

    $defaultFields = [];
    if (empty($type->getBundles())) {
      $enumName = String::formatTypeName("entity:view:modes:$entityTypeId");
      $enumValues = $this->entityManager->getViewModes($entityTypeId);

      $defaultFields['rendered:output'] = [
        'type' => Type::stringType(),
        'args' => !empty($enumValues) ? [
          'viewMode' => [
            'type' => new EnumType($enumName, $enumValues),
          ],
        ] : [],
        'resolve' => [__CLASS__, 'getRenderedOutput'],
        'resolveData' => ['type' => $entityTypeId],
      ];
    }

    $typeFields = array_reduce(array_keys($propertyDefinitions), function ($previous, $propertyKey) use ($propertyDefinitions) {
      $propertyDefinition = $propertyDefinitions[$propertyKey];
      $sanitizedPropertyKey = $propertyKey;

      // Remove the 'field_' prefix for configured fields.
      if ($propertyDefinition instanceof FieldDefinitionInterface && strpos($propertyKey, 'field_') === 0) {
        $sanitizedPropertyKey = substr($propertyKey, 6);
      }

      if ($resolvedProperty = $this->resolveFieldFromProperty($propertyKey, $propertyDefinition)) {
        return $previous + [$sanitizedPropertyKey => $resolvedProperty];
      }

      return $previous;
    }, $defaultFields);

    return $typeFields;
  }

  /**
   * {@inheritdoc}
   */
  protected function resolveFieldFromProperty($propertyKey, DataDefinitionInterface $propertyDefinition) {
    if (!($propertyDefinition instanceof FieldDefinitionInterface)) {
      // Treat non-field properties via the default typed data resolver.
      return parent::resolveFieldFromProperty($propertyKey, $propertyDefinition);
    }

    $storageDefinition = $propertyDefinition->getFieldStorageDefinition();
    $isRequired = $propertyDefinition->isRequired();

    // Skip the list if the cardinality is 1.
    $skipList = $storageDefinition->getCardinality() === 1;

    // Skip the sub-selection if there is just one field item property and it is
    // defined as the main property.
    /** @var \Drupal\Core\TypedData\ComplexDataDefinitionInterface $itemDefinition */
    $itemDefinition = $propertyDefinition->getItemDefinition();
    $subProperties = $itemDefinition->getPropertyDefinitions();
    $mainPropertyName = $itemDefinition->getMainPropertyName();
    $mainProperty = $itemDefinition->getPropertyDefinition($mainPropertyName);
    $skipSubSelection = count($subProperties) === 1 && $mainPropertyName && $mainPropertyName === key($subProperties);

    // Use the default typed data resolver if we can't simplify this field.
    if (!$skipList && !$skipSubSelection) {
      return parent::resolveFieldFromProperty($propertyKey, $propertyDefinition);
    }

    $propertyDefinition = $skipList ? $itemDefinition : $propertyDefinition;
    $propertyDefinition = $skipSubSelection ? $mainProperty : $propertyDefinition;
    $finalResolver = $this->getPropertyResolverFunction($propertyDefinition);

    if (!$propertyType = $this->typeResolver->resolveRecursive($propertyDefinition)) {
      return NULL;
    }

    $propertyType = $skipList ? $propertyType : new ListModifier($propertyType);
    $propertyType = $isRequired ? new NonNullModifier($propertyType) : $propertyType;

    return [
      'type' => $propertyType,
      'resolve' => [__CLASS__, 'getFieldValueSimplified'],
      'resolveData' => [
        'skipList' => $skipList,
        'skipSubSelection' => $skipSubSelection,
        'property' => $propertyKey,
        'subProperty' => $mainPropertyName,
        'finalResolver' => $finalResolver,
      ],
    ];
  }

  /**
   * Object type resolver callback for entity type schema interfaces.
   *
   * @param \Drupal\Core\Entity\Plugin\DataType\EntityAdapter $data
   *   The object type of the given data.
   *
   * @return \Fubhy\GraphQL\Type\Definition\Types\ObjectType|null
   *   The object type of the given data or NULL if it could not be resolved.
   */
  public static function getObjectTypeFromData(EntityAdapter $data) {
    if (!$entity = $data->getValue()) {
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

  /**
   * Property value resolver callback for primitive properties.
   *
   * @param \Drupal\Core\Entity\Plugin\DataType\EntityAdapter $data
   *   The parent complex data structure to extract the property from.
   *
   * @return mixed
   *   The resolved value.
   */
  public static function getFieldValueSimplified(EntityAdapter $data, $a, $b, $c, $d, $e, $f, $config) {
    $skipList = $config['skipList'];
    $skipSubSelection = $config['skipSubSelection'];
    $property = $config['property'];
    $subProperty = $config['subProperty'];
    $finalResolver = $config['finalResolver'];

    $data = $data->get($property);
    if ($data instanceof AccessibleInterface && !$data->access('view')) {
      return NULL;
    }

    $data = $skipList ? [$data->get(0)] : iterator_to_array($data);
    $args = [$a, $b, $c, $d, $e, $f, ['property' => $subProperty]];

    $data = $skipSubSelection ? array_map(function ($item) use ($finalResolver, $args) {
      return call_user_func_array($finalResolver, array_merge([$item], $args));
    }, $data) : $data;

    return $skipList ? reset($data) : $data;
  }

  /**
   * Property value resolver callback for getting the rendered entity output.
   *
   * @param \Drupal\Core\Entity\Plugin\DataType\EntityAdapter $data
   *   The parent complex data structure to extract the entity from.
   *
   * @return mixed
   *   The rendered entity using the given view mode.
   */
  public static function getRenderedOutput(EntityAdapter $data, $args, $b, $c, $d, $e, $f, $config) {
    if ($data instanceof AccessibleInterface && !$data->access('view')) {
      return NULL;
    }

    // @todo Fix injection of container dependencies in resolver functions.
    $viewBuilder = \Drupal::entityManager()->getViewBuilder($config['type']);
    $renderer = \Drupal::service('renderer');

    $output = $viewBuilder->view($data->getValue(), $args['viewMode'] ?: NULL);
    return $renderer->render($output);
  }
}
