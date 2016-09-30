<?php

namespace Drupal\graphql\GraphQL\Type\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\GraphQL\Field\Entity\EntityIdField;
use Drupal\graphql\GraphQL\Field\Entity\EntityTypeField;
use Youshido\GraphQL\Type\InterfaceType\AbstractInterfaceType;

class EntityInterfaceType extends AbstractInterfaceType {
  /**
   * Constructs an EntityInterfaceType object.
   */
  public function __construct() {
    $config = [
      'name' => 'Entity',
      'fields' => [
        'entityId' => new EntityIdField(),
        'entityType' => new EntityTypeField(),
      ],
    ];

    parent::__construct($config);
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

  /**
   * {@inheritdoc}
   */
  public function build($config) {
    // @todo This method should not be required.
  }
}