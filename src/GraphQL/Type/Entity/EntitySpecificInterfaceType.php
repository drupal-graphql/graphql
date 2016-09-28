<?php

namespace Drupal\graphql\GraphQL\Type\Entity;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\graphql\Utility\StringHelper;
use Youshido\GraphQL\Config\Object\InterfaceTypeConfig;
use Youshido\GraphQL\Type\InterfaceType\AbstractInterfaceType;

class EntitySpecificInterfaceType extends AbstractInterfaceType {
  /**
   * Creates an EntityObjectType instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entityType
   *   The entity type definition for this object type.
   */
  public function __construct(EntityTypeInterface $entityType) {
    $typeName = StringHelper::formatTypeName($entityType->id());

    // @todo Build up the fields based on the entity properties and fields.
    $config = [
      'name' => "Entity${$typeName}",
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
    // @todo Implement this.
  }
}