<?php

namespace Drupal\graphql\GraphQL\Field\Root\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\TypedData\EntityDataDefinitionInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\graphql\GraphQL\Field\FieldBase;
use Drupal\graphql\Utility\StringHelper;
use Fubhy\GraphQL\Type\Definition\Types\ModifierInterface;
use Fubhy\GraphQL\Type\Definition\Types\Type;
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
   */
  public function __construct(EntityTypeInterface $entityType, TypeInterface $outputType) {
    $this->entityTypeId = $entityType->id();

    /** @var \Drupal\Core\Entity\TypedData\EntityDataDefinition $definition */
    $definition = \Drupal::typedDataManager()->createDataDefinition("entity:{$this->entityTypeId}");
    $arguments = $this->getQueryArguments($definition);
    $argumentNames = StringHelper::formatPropertyNameList(array_keys($arguments));

    // Generate a human readable name from the entity type.
    $typeName = StringHelper::formatPropertyName($this->entityTypeId);

    $queryValue = new EntityQueryValue($this->entityTypeId, array_flip($argumentNames));

    $config = [
      'name' => "{$typeName}Query",
      'type' => new ListType($outputType),
      'args' => [
          'offset' => ['type' => new IntType()],
          'limit' => ['type' => new IntType()],
        ] + array_combine($argumentNames, $arguments),
      'resolve' => [$queryValue, 'getEntityList'],
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
  protected function getQueryArguments(EntityDataDefinitionInterface $definition) {
    $args = [];
    return $args;
    // setContainer() has not yet been called from Processor::resolveFieldValue()
    // at this point, so we need to fetch it.
    /** @var \Drupal\graphql\TypeResolver\TypeResolverInterface $typeResolver */
    $typeResolver = \Drupal::service('graphql.type_resolver');

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

}
