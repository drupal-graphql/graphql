<?php

namespace Drupal\graphql\GraphQL\Type\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\graphql\GraphQL\Field\Entity\EntityIdField;
use Drupal\graphql\GraphQL\Field\Entity\EntityTypeField;
use Drupal\graphql\GraphQL\Relay\Field\GlobalIdField;
use Drupal\graphql\Utility\StringHelper;
use Youshido\GraphQL\Type\InterfaceType\AbstractInterfaceType;

class EntitySpecificInterfaceType extends AbstractInterfaceType {
  /**
   * Creates an EntitySpecificInterfaceType instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeInterface $entityType
   *   The entity type definition for this object type.
   */
  public function __construct(EntityTypeInterface $entityType) {
    $entityTypeId = $entityType->id();
    $typeName = StringHelper::formatTypeName($entityTypeId);

    $config = [
      'name' => "Entity{$typeName}",
      'fields' => [
        'id' => new GlobalIdField("entity/$entityTypeId"),
        'entityId' => new EntityIdField(),
        'entityType' => new EntityTypeField(),
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