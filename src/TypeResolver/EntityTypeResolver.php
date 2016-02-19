<?php

/**
 * @file
 * Contains \Drupal\graphql\TypeResolver\EntityTypeResolver.
 */

namespace Drupal\graphql\TypeResolver;

use Drupal\Core\Entity\ContentEntityInterface;
use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Entity\TypedData\EntityDataDefinitionInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\TypedData\TypedDataInterface;
use Drupal\Core\TypedData\TypedDataManager;
use Drupal\graphql\TypeResolverInterface;
use Fubhy\GraphQL\Language\Node;
use Fubhy\GraphQL\Type\Definition\Types\EnumType;
use Fubhy\GraphQL\Type\Definition\Types\InterfaceType;
use Fubhy\GraphQL\Type\Definition\Types\NonNullModifier;
use Fubhy\GraphQL\Type\Definition\Types\ObjectType;
use Fubhy\GraphQL\Type\Definition\Types\Type;

/**
 * Resolves typed data types.
 */
class EntityTypeResolver extends ComplexDataTypeResolverBase {
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
   * The renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a EntityTypeResolver object.
   *
   * @param TypeResolverInterface $type_resolver
   *   The base type resolver service.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager service.
   * @param TypedDataManager $typed_data_manager
   *   The typed data manager service.
   * @param RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(TypeResolverInterface $type_resolver, EntityManagerInterface $entity_manager, TypedDataManager $typed_data_manager, RendererInterface $renderer) {
    parent::__construct($type_resolver);

    $this->typedDataManager = $typed_data_manager;
    $this->entityManager = $entity_manager;
    $this->renderer = $renderer;
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
  protected function doResolveRecursive($type) {
    if ($type instanceof EntityDataDefinitionInterface) {
      // We only support content entity types for now.
      $entity_type = $this->entityManager->getDefinition($type->getEntityTypeId());
      if (!$entity_type->isSubclassOf('\Drupal\Core\Entity\ContentEntityInterface')) {
        return NULL;
      }

      $entity_type_id = $type->getEntityTypeId();
      foreach ($this->entityManager->getBundleInfo($entity_type_id) as $bundle_name => $bundle_info) {
        $this->getEntityBundleObject($entity_type_id, $bundle_name);
      }

      if ($resolved = $this->getEntityTypeInterface($entity_type_id)) {
        return $type->isRequired() ? new NonNullModifier($resolved) : $resolved;
      }
    }

    return NULL;
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
          return $this->renderer->renderRoot($output);
        }

        return NULL;
      }
    ] : NULL;
  }
}
