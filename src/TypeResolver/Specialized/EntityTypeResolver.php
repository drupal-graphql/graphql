<?php

/**
 * @file
 * Contains \Drupal\graphql\TypeResolver\Specialized\EntityTypeResolver.
 */

namespace Drupal\graphql\TypeResolver\Specialized;

use Drupal\Core\Access\AccessibleInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\Plugin\DataType\EntityAdapter;
use Drupal\Core\Entity\TypedData\EntityDataDefinitionInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\TypedData\ComplexDataDefinitionInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\TypedDataManager;
use Drupal\graphql\TypeResolver\Generic\ComplexDataTypeResolver;
use Drupal\graphql\TypeResolverInterface;
use Drupal\graphql\Utility\String;
use Fubhy\GraphQL\Language\Node;
use Fubhy\GraphQL\Type\Definition\Types\InterfaceType;
use Fubhy\GraphQL\Type\Definition\Types\ListModifier;
use Fubhy\GraphQL\Type\Definition\Types\NonNullModifier;
use Fubhy\GraphQL\Type\Definition\Types\ObjectType;

/**
 * Resolves typed data types.
 */
class EntityTypeResolver extends ComplexDataTypeResolver {
  /**
   * Static cache of resolved schema object types.
   *
   * @var array
   */
  protected $objectCache = [];

  /**
   * Static cache of resolved schema interface types.
   *
   * @var array
   */
  protected $interfaceCache = [];

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
   * Constructs a EntityTypeResolver object.
   *
   * @param TypeResolverInterface $type_resolver
   *   The base type resolver service.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager service.
   * @param TypedDataManager $typed_data_manager
   *   The typed data manager service.
   */
  public function __construct(TypeResolverInterface $type_resolver, EntityManagerInterface $entity_manager, TypedDataManager $typed_data_manager) {
    parent::__construct($type_resolver);
    $this->typedDataManager = $typed_data_manager;
    $this->entityManager = $entity_manager;
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
    if (!($type instanceof EntityDataDefinitionInterface)) {
      return NULL;
    }

    /** @var EntityDataDefinitionInterface $definition */
    $entity_type_id = $type->getEntityTypeId();
    $definition = $this->typedDataManager->createDataDefinition("entity:$entity_type_id");

    $key = $this->getTypeIdentifier($definition);
    if (array_key_exists($key, $this->interfaceCache)) {
      return $this->interfaceCache[$key];
    }

    return function () use ($type) {
      $entity_type_id = $type->getEntityTypeId();
      foreach ($this->entityManager->getBundleInfo(
        $entity_type_id
      ) as $bundle_name => $bundle_info) {
        $this->getEntityBundleObject($entity_type_id, $bundle_name);
      }

      if ($resolved = $this->getEntityTypeInterface($entity_type_id)) {
        return $type->isRequired() ? new NonNullModifier($resolved) : $resolved;
      }
    };
  }

  /**
   * {@inheritdoc}
   */
  protected function getTypeIdentifier(ComplexDataDefinitionInterface $definition) {
    /** @var EntityDataDefinitionInterface $definition */
    $type = 'entity';
    if ($entity_type = $definition->getEntityTypeId()) {
      $type .= ':' . $entity_type;
      if (($bundles = $definition->getBundles()) && count($bundles) === 1) {
        $bundle = reset($bundles);
        $type .= ':' . $bundle;
      }
    }

    return $type;
  }

  /**
   * @param $entity_type_id
   * @param $bundle_name
   *
   * @return \Fubhy\GraphQL\Type\Definition\Types\ObjectType
   */
  protected function getEntityBundleObject($entity_type_id, $bundle_name) {
    /** @var EntityDataDefinitionInterface $definition */
    $definition = $this->typedDataManager->createDataDefinition("entity:$entity_type_id");
    $definition->setBundles([$bundle_name]);

    $key = $this->getTypeIdentifier($definition);
    if (array_key_exists($key, $this->objectCache)) {
      return $this->objectCache[$key];
    }

    // Initialize the static cache entry.
    $cache = &$this->objectCache[$key];

    if ($fields = $this->getFieldsFromPropertiesOrSchema($definition)) {
      $interface = $this->getEntityTypeInterface($entity_type_id);
      $name = $this->getTypeName($definition);
      $cache = new ObjectType($name, $fields, [$interface]);
    }

    return $cache;
  }

  /**
   * @param $entity_type_id
   *
   * @return \Fubhy\GraphQL\Type\Definition\Types\InterfaceType
   */
  protected function getEntityTypeInterface($entity_type_id) {
    /** @var EntityDataDefinitionInterface $definition */
    $definition = $this->typedDataManager->createDataDefinition("entity:$entity_type_id");

    $key = $this->getTypeIdentifier($definition);
    if (array_key_exists($key, $this->interfaceCache)) {
      return $this->interfaceCache[$key];
    }

    // Initialize the static cache entry.
    $cache = &$this->interfaceCache[$key];

    if ($fields = $this->getFieldsFromPropertiesOrSchema($definition)) {
      $name = $this->getTypeName($definition);
      $cache = new InterfaceType($name, $fields, [__CLASS__, 'resolveObjectType']);
    }

    return $cache;
  }

  protected function getFieldsFromPropertiesOrSchema(ComplexDataDefinitionInterface $definition) {
    /** @var EntityDataDefinitionInterface $definition */
    $entity_definition = $this->entityManager->getDefinition($definition->getEntityTypeId());
    $entity_implements = class_implements($entity_definition->getClass());

    $fields = [];
    if (in_array('Drupal\Core\Entity\ContentEntityInterface', $entity_implements)) {
      $fields = $this->getFieldsFromProperties($definition);
    }
    else if (in_array('Drupal\Core\Config\Entity\ConfigEntityInterface', $entity_implements)) {
      $fields = $this->getFieldsFromSchema($definition);
    }

    // @todo Properly assign references to the interface/bundle.
    $entity_type_id = $definition->getEntityTypeId();
    $references_map = $this->getReferencesMap();
    $references = isset($references_map[$entity_type_id]) ? $references_map[$entity_type_id] : [];

    foreach ($references as $origin => $reference_fields) {
      $origin_definition = $this->typedDataManager->createDataDefinition("entity:$origin");
      if (!$referencing_type = $this->resolveRecursive($origin_definition)) {
        continue;
      }

      foreach ($reference_fields as $reference_field) {
        $name = "referencing:$origin:via:$reference_field";
        $name = String::formatPropertyName($name);
        $name = String::ensureUnambiguousness($name, array_keys($fields));

        $fields[$name] = [
          'type' => new NonNullModifier(new ListModifier($referencing_type)),
          'resolve' => [__CLASS__, 'resolveReferencingEntities'],
          'resolveData' => [
            'origin' => $origin,
            'field' => $reference_field,
          ],
        ];
      }
    }

    return $fields;
  }

  /**
   * @param ComplexDataDefinitionInterface $definition
   *
   * @return array
   */
  protected function getFieldsFromSchema(ComplexDataDefinitionInterface $definition) {
    // TODO Add typed config schema based resolution of config entities.
    $fields = [];

    return $fields;
  }

  /**
   * @param \Drupal\Core\TypedData\ComplexDataDefinitionInterface $definition
   * @param \Drupal\Core\TypedData\DataDefinitionInterface $property
   * @param string $key
   *
   * @return array
   */
  protected function getFieldFromProperty(ComplexDataDefinitionInterface $definition, DataDefinitionInterface $property, $key) {
    if (!($property instanceof FieldDefinitionInterface)) {
      // Treat everything except field definitions normally.
      return parent::getFieldFromProperty($definition, $property, $key);
    }

    $storage = $property->getFieldStorageDefinition();
    $required = $property->isRequired();
    $multiple = $storage->isMultiple();

    if (count($storage->getPropertyNames()) === 1 && $main = $storage->getMainPropertyName()) {
      $property = $storage->getPropertyDefinition($main);
    }
    else {
      $property = $property->getItemDefinition();
    }

    if (!$type = $this->typeResolver->resolveRecursive($property)) {
      return FALSE;
    }

    $type = $multiple ? new ListModifier($type) : $type;
    $type = $required ? new NonNullModifier($type) : $type;

    return [
      'type' => $type,
      'resolve' => [__CLASS__, 'resolvePropertyValue'],
      'resolveData' => [
        'key' => $key,
      ],
    ];
  }

  /**
   * @return array
   */
  protected function getReferencesMap() {
    if (!isset($this->referencesMap)) {
      $this->referencesMap = [];

      $reference_fields = $this->entityManager->getFieldMapByFieldType('entity_reference');
      foreach ($reference_fields as $origin => $fields) {
        $storage_definitions = $this->entityManager->getFieldStorageDefinitions($origin);

        $intersection = array_intersect_key($storage_definitions, $fields);
        foreach ($intersection as $name => $storage) {
          $target = $storage->getSetting('target_type');
          $this->referencesMap[$target][$origin][$name] = $name;
        }
      }
    }

    return $this->referencesMap;
  }

  /**
   * @param mixed $source
   * @param array|NULL $args
   * @param mixed $root
   * @param \Fubhy\GraphQL\Language\Node $field
   *
   * @return \Drupal\Core\Field\FieldItemListInterface|mixed|null
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public static function resolvePropertyValue($source, array $args = NULL, $root, Node $field, $a, $b, $c, $data) {
    if ($source instanceof EntityAdapter) {
      /** @var FieldItemListInterface $value */
      if (($value = $source->get($data['key'])) === NULL) {
        return NULL;
      }

      $storage = $value->getFieldDefinition()->getFieldStorageDefinition();
      $multiple = $storage->isMultiple();
      if ($value instanceof AccessibleInterface && !$value->access('view')) {
        return NULL;
      }

      $value = $multiple ? iterator_to_array($value) : [$value->first()];

      if (count($storage->getPropertyNames()) === 1 && $main = $storage->getMainPropertyName()) {
        $value = array_filter(array_map(function ($item) use ($main) {
          if ($item instanceof AccessibleInterface && !$item->access('view')) {
            return NULL;
          }

          return $item->get($main)->getValue();
        }, $value));
      }

      return $multiple ? $value : reset($value);
    }

    return NULL;
  }
  /**
   * @param mixed $source
   * @param array|NULL $args
   * @param mixed $root
   * @param \Fubhy\GraphQL\Language\Node $field
   *
   * @return \Drupal\Core\Field\FieldItemListInterface|mixed|null
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public static function resolveReferencingEntities($source, array $args = NULL, $root, Node $field, $a, $b, $c, $data) {
    if ($source instanceof EntityAdapter) {
      $entity_manager = \Drupal::entityManager();
      $storage = $entity_manager->getStorage($data['origin']);
      $query = $storage->getQuery()
        ->accessCheck()
        ->range(0, 10)
        ->condition($data['field'], $source->getValue()->id());

      if (!$results = $query->execute()) {
        return [];
      }

      return array_filter(array_map(function ($entity) {
        if ($entity instanceof AccessibleInterface && !$entity->access('view')) {
          return NULL;
        }

        return $entity->getTypedData();
      }, $storage->loadMultiple($results)));
    }

    return NULL;
  }

  /**
   * @param $source
   * @return null
   */
  public static function resolveObjectType($source) {
    if ($source instanceof EntityAdapter && $entity = $source->getValue()) {
      $language = \Drupal::service('language_manager')->getCurrentLanguage();
      $schema = \Drupal::service('graphql.schema_loader')->loadSchema($language);
      $types = $schema->getTypeMap();

      $type = "entity:{$entity->getEntityType()->id()}:{$entity->bundle()}";
      $type = String::formatTypeName($type);

      if (isset($types[$type])) {
        return $types[$type];
      }
    }

    return NULL;
  }
}
