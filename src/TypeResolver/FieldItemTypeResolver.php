<?php

/**
 * @file
 * Contains \Drupal\graphql\TypeResolver\FieldItemTypeResolver.
 */

namespace Drupal\graphql\TypeResolver;

use Drupal\Core\Entity\EntityManagerInterface;
use Drupal\Core\Field\FieldItemInterface;
use Drupal\Core\Field\TypedData\FieldItemDataDefinition;
use Drupal\Core\Render\RendererInterface;
use Drupal\graphql\TypeResolverInterface;
use Fubhy\GraphQL\Language\Node;
use Fubhy\GraphQL\Type\Definition\Types\EnumType;
use Fubhy\GraphQL\Type\Definition\Types\NonNullModifier;
use Fubhy\GraphQL\Type\Definition\Types\ObjectType;
use Fubhy\GraphQL\Type\Definition\Types\Type;

/**
 * Resolves typed data types.
 */
class FieldItemTypeResolver extends ComplexDataTypeResolverBase {
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
   * Constructs a FieldItemTypeResolver object.
   *
   * @param TypeResolverInterface $type_resolver
   *   The base type resolver service.
   * @param \Drupal\Core\Entity\EntityManagerInterface $entity_manager
   *   The entity manager service.
   * @param RendererInterface $renderer
   *   The renderer service.
   */
  public function __construct(TypeResolverInterface $type_resolver, EntityManagerInterface $entity_manager, RendererInterface $renderer) {
    parent::__construct($type_resolver);

    $this->entityManager = $entity_manager;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public function applies($type) {
    return $type instanceof FieldItemDataDefinition;
  }

  /**
   * {@inheritdoc}
   */
  protected function doResolveRecursive($type) {
    if ($type instanceof FieldItemDataDefinition) {
      $field_definition = $type->getFieldDefinition();
      $entity_type_id = $field_definition->getTargetEntityTypeId();
      $name = $field_definition->getName();
      if (array_key_exists("field:item:$entity_type_id:$name", $this->fieldItemTypes)) {
        return $this->fieldItemTypes["field:item:$entity_type_id:$name"];
      }

      // Initialize the static cache entry.
      $cache = &$this->fieldItemTypes["field:item:$entity_type_id:$name"];

      $fields = $this->getFieldsFromProperties($type);
      if ($rendered = $this->getRenderedFieldItemField($entity_type_id)) {
        $fields['__rendered'] = $rendered;
      }

      if (!empty($fields)) {
        return $cache = new ObjectType($this->stringToName("field:item:$entity_type_id:$name"), $fields);
      }

      return $cache;
    }

    return NULL;
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
          return $this->renderer->renderRoot($output);
        }

        return NULL;
      }
    ] : NULL;
  }
}
