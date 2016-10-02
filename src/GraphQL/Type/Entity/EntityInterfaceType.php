<?php

namespace Drupal\graphql\GraphQL\Type\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\GraphQL\Field\Entity\EntityIdField;
use Drupal\graphql\GraphQL\Field\Entity\EntityTypeField;
use Drupal\graphql\GraphQL\Relay\Field\GlobalIdField;
use Drupal\graphql\GraphQL\Type\AbstractInterfaceType;

class EntityInterfaceType extends AbstractInterfaceType {
  /**
   * Constructs an EntityInterfaceType object.
   */
  public function __construct() {
    $config = [
      'name' => 'Entity',
      'fields' => [
        'id' => new GlobalIdField('entity'),
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
    // @todo Use the type resolver service here instead.
    if ($object instanceof EntityInterface) {
      return new EntityObjectType($object->getEntityType(), $object->bundle());
    }

    return NULL;
  }
}