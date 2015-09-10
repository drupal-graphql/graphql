<?php

/**
 * @file
 * Contains \Drupal\graphql\TypedDataTypeResolver.
 */

namespace Drupal\graphql;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\TypedData\EntityDataDefinitionInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\TypedData\FieldItemDataDefinition;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\TypedData\ComplexDataDefinitionInterface;
use Drupal\Core\TypedData\ComplexDataInterface;
use Drupal\Core\TypedData\DataDefinitionInterface;
use Drupal\Core\TypedData\DataReferenceDefinitionInterface;
use Drupal\Core\TypedData\DataReferenceInterface;
use Drupal\Core\TypedData\ListDataDefinitionInterface;
use Drupal\Core\TypedData\ListInterface;
use Drupal\Core\TypedData\Plugin\DataType\Language;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\Core\TypedData\TypedDataManager;
use Fubhy\GraphQL\Language\Node;
use Fubhy\GraphQL\Type\Definition\Types\EnumType;
use Fubhy\GraphQL\Type\Definition\Types\InterfaceType;
use Fubhy\GraphQL\Type\Definition\Types\ListModifier;
use Fubhy\GraphQL\Type\Definition\Types\NonNullModifier;
use Fubhy\GraphQL\Type\Definition\Types\ObjectType;
use Fubhy\GraphQL\Type\Definition\Types\Scalars\NullType;
use Fubhy\GraphQL\Type\Definition\Types\Type;

/**
 * Resolves typed data types.
 */
class TypedDataTypeResolver implements TypeResolverInterface {
  use StringTranslationTrait;

  /**
   * @var \Fubhy\GraphQL\Type\Definition\Types\ObjectType
   */
  protected static $language;

  /**
   * @return \Fubhy\GraphQL\Type\Definition\Types\ObjectType
   */
  public static function languageType() {
    if (!isset(static::$language)) {
      static::$language = new ObjectType('Language', [
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
      ]);
    }

    return static::$language;
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

  /**
   * Static cache of schema interface types for entity types.
   *
   * @var array
   */
  protected $entityTypeInterfaces = [];

  /**
   * Static cache of schema object types for entity bundles.
   *
   * @var array
   */
  protected $entityBundleObjects = [];

  /**
   * Static cache of schema object types for entity fields.
   *
   * @var array
   */
  protected $fieldItemTypes = [];

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
   * Constructs a EntitySchemaProvider object.
   *
   * @param \Drupal\Core\Entity\EntityManagerInterface $entityManager
   *   The entity manager service.
   * @param TypedDataManager $typedDataManager
   *   The typed data manager service.
   */
  public function __construct(EntityManagerInterface $entityManager, TypedDataManager $typedDataManager, RendererInterface $renderer) {
    $this->typedDataManager = $typedDataManager;
    $this->entityManager = $entityManager;
    $this->renderer = $renderer;
  }

  /**
   * @param mixed $type
   *
   * @return bool
   */
  public function applies($type) {
    return $type instanceof DataDefinitionInterface;
  }

  /**
   * @param mixed$definition
   * @param bool $defer
   *
   * @return \Fubhy\GraphQL\Type\Definition\Types\TypeInterface|callable|null
   */
  public function resolveRecursive($definition, $defer = TRUE) {
    // Prevent infinite loops by resolving complex data definitions lazily
    // by returning a closure unless specified otherwise.
    if ($defer && $definition instanceof ComplexDataDefinitionInterface) {
      return function () use ($definition) {
        // Optimally, we would also only return NULL here but since that breaks
        // the whole thing we need to invent a null schema type.
        // @todo Revisit this later to try and find a better solution.
        return $this->doResolveRecursive($definition) ?: new NullType();
      };
    }

    return $this->doResolveRecursive($definition);
  }

  /**
   * @param mixed $definition
   *
   * @return \Fubhy\GraphQL\Type\Definition\Types\TypeInterface|null
   */
  protected function doResolveRecursive($definition) {
    $type = NULL;

    if ($definition instanceof ListDataDefinitionInterface) {
      $type = $this->resolveRecursive($definition->getItemDefinition());
      $type = $type ? new ListModifier($type) : NULL;
    }
    else if ($definition instanceof DataReferenceDefinitionInterface) {
      $type = $this->resolveRecursive($definition->getTargetDefinition()) ?: NULL;
    }
    else if ($definition instanceof ComplexDataDefinitionInterface) {
      if ($definition instanceof EntityDataDefinitionInterface) {
        $type = $this->resolveEntityDataDefinition($definition);
      }
      else if ($definition instanceof FieldItemDataDefinition) {
        $type = $this->resolveFieldItemDataDefinition($definition);
      }
    }
    else if ($definition instanceof DataDefinitionInterface) {
      // This seems to be a primitive and other simple data types.
      switch ($definition->getDataType()) {
        case 'integer':
        case 'timestamp':
          $type = Type::intType();
          break;
        case 'string':
        case 'email':
        case 'uri':
          $type = Type::stringType();
          break;
        case 'boolean':
          $type = Type::booleanType();
          break;
        case 'float':
          $type = Type::floatType();
          break;
        case 'language':
          $type = static::languageType();
          break;
      }
    }

    return $type && $definition->isRequired() ? new NonNullModifier($type) : $type;
  }

  /**
   * @param ComplexDataDefinitionInterface $definition
   *
   * @return array
   */
  protected function getFieldsFromProperties(ComplexDataDefinitionInterface $definition) {
    return array_filter(array_map(function (DataDefinitionInterface $property) {
      if (!$type = $this->resolveRecursive($property)) {
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
   * @param $entity_type_id
   * @param $bundle_name
   *
   * @return \Fubhy\GraphQL\Type\Definition\Types\ObjectType
   */
  protected function getEntityBundleObject($entity_type_id, $bundle_name) {
    if (array_key_exists("$entity_type_id:$bundle_name", $this->entityBundleObjects)) {
      return $this->entityBundleObjects["$entity_type_id:$bundle_name"];
    }

    // Initialize the static cache entry.
    $cache = &$this->entityBundleObjects["$entity_type_id:$bundle_name"];

    /** @var EntityDataDefinitionInterface $definition */
    $definition = $this->typedDataManager->createDataDefinition("entity:$entity_type_id");
    $definition->setBundles([$bundle_name]);

    // No point in building a schema for an object without properties.
    $fields = $this->getFieldsFromProperties($definition);
    if ($rendered = $this->getRenderedEntityField($entity_type_id)) {
      $fields['__rendered'] = $rendered;
    }

    if (!empty($fields)) {
      $interface = $this->getEntityTypeInterface($entity_type_id);
      $cache = new ObjectType($this->stringToName("entity:$entity_type_id:$bundle_name"), $fields, [$interface]);
    }

    return $cache;
  }

  /**
   * @param $entity_type_id
   *
   * @return \Fubhy\GraphQL\Type\Definition\Types\InterfaceType
   */
  protected function getEntityTypeInterface($entity_type_id) {
    if (array_key_exists($entity_type_id, $this->entityTypeInterfaces)) {
      return $this->entityTypeInterfaces[$entity_type_id];
    }

    // Initialize the static cache entry.
    $cache = &$this->entityTypeInterfaces[$entity_type_id];

    /** @var EntityDataDefinitionInterface $definition */
    $definition = $this->typedDataManager->createDataDefinition("entity:$entity_type_id");

    // No point in building a schema for an object without properties.
    $fields = $this->getFieldsFromProperties($definition);
    if ($rendered = $this->getRenderedEntityField($entity_type_id)) {
      $fields['__rendered'] = $rendered;
    }

    if (!empty($fields)) {
      $cache = new InterfaceType($this->stringToName("entity:$entity_type_id"), $fields, function ($source) {
        if ($source instanceof TypedDataInterface) {
          if (($entity = $source->getValue()) instanceof ContentEntityInterface) {
            return $this->getEntityBundleObject($entity->getEntityTypeId(), $entity->bundle());
          }
        }

        return NULL;
      });
    }

    return $cache;
  }

  /**
   * @param \Drupal\Core\Entity\TypedData\EntityDataDefinitionInterface $definition
   *
   * @return \Fubhy\GraphQL\Type\Definition\Types\InterfaceType|null
   */
  protected function resolveEntityDataDefinition(EntityDataDefinitionInterface $definition) {
    // We only support content entity types for now.
    $entity_type = $this->entityManager->getDefinition($definition->getEntityTypeId());
    if (!$entity_type->isSubclassOf('\Drupal\Core\Entity\ContentEntityInterface')) {
      return NULL;
    }

    $entity_type_id = $definition->getEntityTypeId();
    foreach ($this->entityManager->getBundleInfo($entity_type_id) as $bundle_name => $bundle_info) {
      $this->getEntityBundleObject($entity_type_id, $bundle_name);
    }

    return $this->getEntityTypeInterface($entity_type_id);
  }

  /**
   * @param \Drupal\Core\Field\TypedData\FieldItemDataDefinition $definition
   *
   * @return \Fubhy\GraphQL\Type\Definition\FieldDefinition|null
   */
  protected function resolveFieldItemDataDefinition(FieldItemDataDefinition $definition) {
    $field_definition = $definition->getFieldDefinition();
    $entity_type_id = $field_definition->getTargetEntityTypeId();
    $name = $field_definition->getName();
    if (array_key_exists("field:item:$entity_type_id:$name", $this->fieldItemTypes)) {
      return $this->fieldItemTypes["field:item:$entity_type_id:$name"];
    }

    // Initialize the static cache entry.
    $cache = &$this->fieldItemTypes["field:item:$entity_type_id:$name"];

    $fields = $this->getFieldsFromProperties($definition);
    if ($rendered = $this->getRenderedFieldItemField($entity_type_id)) {
      $fields['__rendered'] = $rendered;
    }

    if (!empty($fields)) {
      return $cache = new ObjectType($this->stringToName("field:item:$entity_type_id:$name"), $fields);
    }

    return $cache;
  }

  /**
   * @param string $entity_type_id
   *
   * @return array|null
   */
  protected function getRenderedEntityField($entity_type_id) {
    $name = $this->stringToName("display:modes:entity:$entity_type_id");
    $modes = array_map(function ($value) {
      return ['description' => $value['label']];
    }, $this->entityManager->getViewModes($entity_type_id));

    return $modes ? [
      'type' => new NonNullModifier(Type::stringType()),
      'args' => [
        'displayMode' => [
          'type' => new EnumType($name, $modes),
        ],
      ],
      'resolve' => function ($source, array $args = NULL) {
        if ($source instanceof TypedDataInterface && ($entity = $source->getValue()) && $entity instanceof ContentEntityInterface) {
          $entity_view_builder = $this->entityManager->getViewBuilder($entity->getEntityTypeId());
          $display_mode = $args['displayMode'] ? $args['displayMode'] : 'full';
          $output = $entity_view_builder->view($entity, $display_mode);
          return $this->renderer->render($output);
        }
      }
    ] : NULL;
  }

  /**
   * @param string $entity_type_id
   *
   * @return array|null
   */
  protected function getRenderedFieldItemField($entity_type_id) {
    $name = $this->stringToName("display:modes:entity:$entity_type_id");
    $modes = array_map(function ($value) {
      return ['description' => $value['label']];
    }, $this->entityManager->getViewModes($entity_type_id));

    return $modes ? [
      'type' => new NonNullModifier(Type::stringType()),
      'args' => [
        'displayMode' => [
          'type' => new EnumType($name, $modes),
        ],
      ],
      'resolve' => function ($source, array $args = NULL) {
        if ($source instanceof FieldItemInterface) {
          $entity_type_id = $source->getEntity()->getEntityTypeId();
          $entity_view_builder = $this->entityManager->getViewBuilder($entity_type_id);
          $display_mode = $args['displayMode'] ? $args['displayMode'] : 'full';
          $output = $entity_view_builder->viewFieldItem($source, $display_mode);
          return $this->renderer->render($output);
        }
      }
    ] : NULL;
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
}
