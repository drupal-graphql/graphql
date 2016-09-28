<?php

namespace Drupal\graphql\GraphQL\Field\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\GraphQL\Field\FieldBase;
use Youshido\GraphQL\Type\NonNullType;
use Youshido\GraphQL\Type\Scalar\StringType;

class EntityIdField extends FieldBase {
  /**
   * Constructs an EntityIdField object.
   */
  public function __construct() {
    $config = [
      'name' => 'entityId',
      'type' => new NonNullType(new StringType()),
    ];

    parent::__construct($config);
  }

  /**
   * Resolve function for this field.
   *
   * Loads the entity id for the given entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $value
   *   The parent value (entity object).
   *
   * @return string
   *   The associated entity id as a string.
   */
  public function resolve(EntityInterface $value) {
    return $value->id();
  }
}