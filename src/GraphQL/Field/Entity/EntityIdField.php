<?php

namespace Drupal\graphql\GraphQL\Field\Entity;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityInterface;
use Drupal\graphql\GraphQL\CacheableLeafValue;
use Drupal\graphql\GraphQL\Field\FieldBase;
use Youshido\GraphQL\Execution\ResolveInfo;
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
   *   The parent value (entity object). May not use language-level type hinting
   *   to keep compatibility with the parent implementation.
   * @param array $args
   *   The field arguments.
   * @param \Youshido\GraphQL\Execution\ResolveInfo $info
   *   The context information to resolve.
   *
   * @return CacheableLeafValue The associated entity id as a string.
   *   The associated entity id as a string.
   */
  public function resolve($value, array $args, ResolveInfo $info) {
    return new CacheableLeafValue($value->getEntityType(), [$value]);
  }

}
