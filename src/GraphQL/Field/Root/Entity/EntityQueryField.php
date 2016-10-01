<?php

namespace Drupal\graphql\GraphQL\Field\Root\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\TypedData\EntityDataDefinitionInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\graphql\GraphQL\Field\FieldBase;
use Drupal\graphql\TypeResolver\TypeResolverInterface;
use Drupal\graphql\Utility\StringHelper;
use Fubhy\GraphQL\Type\Definition\Types\ModifierInterface;
use Symfony\Component\DependencyInjection\ContainerAwareInterface;
use Symfony\Component\DependencyInjection\ContainerAwareTrait;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Type\Enum\EnumType;
use Youshido\GraphQL\Type\ListType\ListType;
use Youshido\GraphQL\Type\Scalar\AbstractScalarType;
use Youshido\GraphQL\Type\Scalar\IntType;
use Youshido\GraphQL\Type\TypeInterface;

class EntityQueryField extends FieldBase implements ContainerAwareInterface {
  use ContainerAwareTrait;

  /**
   * The entity type handled by this field instance.
   *
   * @var string
   */
  protected $entityTypeId;

  /**
   * Constructs an EntityQueryField object.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entityType
   *   The entity type handled by this field instance.
   * @param \Youshido\GraphQL\Type\TypeInterface $outputType
   *   The output type of the field.
   * @param \Drupal\Core\TypedData\TypedDataManagerInterface $typedDataManager
   *   The typed_data_manager service.
   */
  public function __construct(
    EntityTypeInterface $entityType,
    TypeInterface $outputType,
    TypedDataManagerInterface $typedDataManager,
    TypeResolverInterface $typeResolver) {
    $this->entityTypeId = $entityType->id();

    /** @var \Drupal\Core\Entity\TypedData\EntityDataDefinition $definition */
    $definition = $typedDataManager->createDataDefinition("entity:{$this->entityTypeId}");
    $arguments = $this->getQueryArguments($definition, $typeResolver);
    $argumentNames = StringHelper::formatPropertyNameList(array_keys($arguments));

    // Generate a human readable name from the entity type.
    $typeName = StringHelper::formatPropertyName($this->entityTypeId);

    $this->args = array_flip($argumentNames);

    $config = [
      'name' => "{$typeName}Query",
      'type' => new ListType($outputType),
      'args' => [
          'offset' => ['type' => new IntType()],
          'limit' => ['type' => new IntType()],
        ] + array_combine($argumentNames, $arguments),
    ];

    parent::__construct($config);
  }

  /**
   * Utility function to retrieve the list of arguments for an entity query.
   *
   * @param \Drupal\Core\Entity\TypedData\EntityDataDefinitionInterface $definition
   *   The entity type definition.
   *
   * @return array
   *   The list of arguments for potential use in the entity query.
   */
  protected function getQueryArguments(
    EntityDataDefinitionInterface $definition,
    TypeResolverInterface $typeResolver) {
    $args = [];

    foreach ($definition->getPropertyDefinitions() as $fieldName => $fieldDefinition) {
      if (!($fieldDefinition instanceof FieldDefinitionInterface)) {
        continue;
      }

      $storage = $fieldDefinition->getFieldStorageDefinition();
      if (!$storage->isQueryable()) {
        continue;
      };

      // Fetch the main property's definition and resolve it's type.
      $mainPropertyName = $storage->getMainPropertyName();
      $mainProperty = $storage->getPropertyDefinition($mainPropertyName);
      if (!$propertyType = $typeResolver->resolveRecursive($mainProperty)) {
        continue;
      }

      $wrappedType = $propertyType;

      // Extract the wrapped type of the main property.
      while ($wrappedType instanceof ModifierInterface) {
        $wrappedType = $wrappedType->getWrappedType();
      }

      // We only support scalars and enums as arguments.
      if (!($wrappedType instanceof AbstractScalarType || $wrappedType instanceof EnumType)) {
        continue;
      }

      $args[$fieldName] = [
        'type' => new ListType($wrappedType),
        'description' => $fieldDefinition->getDescription(),
      ];
    }

    return $args;
  }

  /**
   * Entity list resolver callback.
   */
  public function resolve($parent, array $args = NULL, ResolveInfo $info) {
    $storage = $this->container->get('entity_type.manager')->getStorage($this->entityTypeId);
    $query = $storage->getQuery()->accessCheck(TRUE);

    $rangeArgs = array('offset', 'limit');
    $filterArgs = array_diff_key($args, array_flip($rangeArgs));
    foreach ($filterArgs as $key => $arg) {
      if (isset($arg) && isset($data['args'][$key])) {
        $arg = is_array($arg) && sizeof($arg) === 1 ? reset($arg) : $arg;
        $operator = is_array($arg) ? 'IN' : '=';
        $query->condition($data['args'][$key], $arg, $operator);
      }
    }

    if (!empty($args['offset']) || !empty($args['limit'])) {
      $query->range($args['offset'] ?: NULL, $args['limit'] ?: NULL);
    }

    $result = $query->execute();
    if (!empty($result)) {
      $entities = $storage->loadMultiple($result);

      // Filter entities that the current user doesn't have view access for.
      return array_filter($entities, function (EntityInterface $entity) {
        return $entity->access('view');
      });
    }

    return [];
  }

}
