<?php

namespace Drupal\graphql\GraphQL\Field\Entity;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\GraphQL\CacheableValue;
use Drupal\graphql\GraphQL\Field\FieldBase;
use Drupal\graphql\GraphQL\Type\EntityType\EntityTypeObjectType;
use Youshido\GraphQL\Execution\ResolveInfo;
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
   * @return CacheableValue The associated entity id as a string.
   *   The associated entity id as a string.
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    return new CacheableValue($value->getEntityType(), [$value]);
  }
}
