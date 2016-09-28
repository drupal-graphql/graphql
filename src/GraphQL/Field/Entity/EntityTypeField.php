<?php

namespace Drupal\graphql\GraphQL\Field\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\GraphQL\Field\FieldBase;
use Drupal\graphql\GraphQL\Type\EntityType\EntityTypeObjectType;
use Youshido\GraphQL\Type\NonNullType;

class EntityTypeField extends FieldBase {
  /**
   * Constructs an EntityTypeField object.
   */
  public function __construct() {
    $config = [
      'name' => 'entityType',
      'type' => new NonNullType(new EntityTypeObjectType()),
    ];

    parent::__construct($config);
  }

  /**
   * Resolve function for this field.
   *
   * Loads the entity type object for the given entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $value
   *   The parent value (entity object).
   *
   * @return \Drupal\Core\Entity\EntityTypeInterface|null
   *   The associated entity type object.
   */
  public function resolve(EntityInterface $value) {
    return $value->getEntityType();
  }
}