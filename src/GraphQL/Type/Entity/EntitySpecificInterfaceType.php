<?php

namespace Drupal\graphql\GraphQL\Type\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\graphql\Utility\StringHelper;
use Youshido\GraphQL\Type\InterfaceType\AbstractInterfaceType;
use Youshido\GraphQL\Type\Scalar\StringType;

class EntitySpecificInterfaceType extends AbstractInterfaceType {
  /**
   * Creates an EntitySpecificInterfaceType instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entityType
   *   The entity type definition for this object type.
   */
  public function __construct(EntityTypeInterface $entityType) {
    $typeName = StringHelper::formatTypeName($entityType->id());

    $config = [
      'name' => "Entity{$typeName}",
      'fields' => [
        'placeholder' => [
          'type' => new StringType(),
        ],
      ],
    ];

    parent::__construct($config);
  }

  /**
   * {@inheritdoc}
   */
  public function build($config) {
    // @todo This method should not be required.
  }

  /**
   * {@inheritdoc}
   */
  public function resolveType($object) {
    if ($object instanceof EntityInterface) {
      return new EntityObjectType($object->getEntityType());
    }

    return NULL;
  }
}