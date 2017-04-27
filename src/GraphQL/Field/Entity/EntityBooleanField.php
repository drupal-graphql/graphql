<?php

namespace Drupal\graphql\GraphQL\Field\Entity;

use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\GraphQL\Field\FieldBase;
use Youshido\GraphQL\Execution\ResolveInfo;
use Youshido\GraphQL\Type\Scalar\BooleanType;

class EntityBooleanField extends FieldBase {
  /**
   * Constructs an EntityBooleanField object.
   */
  public function __construct($fieldDefinition) {
    $config = [
      'name' => $fieldDefinition->getName(),
      'type' => new BooleanType()
    ];

    parent::__construct($config);
  }

  /**
   * Resolve function for this field.
   *
   * Loads the entity id for the given entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $value
   *   The parent value (entity object). May not use language-level type hinting
   *   to keep compatibility with the parent implementation.
   * @param array $args
   *   The field arguments.
   * @param \Youshido\GraphQL\Execution\ResolveInfo $info
   *   The context information for which to resolve.
   *
   * @return @TODO
   *   @TODO
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    return $value->get($this->getName())->getValue();
  }
}
