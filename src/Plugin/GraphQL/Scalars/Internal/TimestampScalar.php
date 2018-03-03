<?php

namespace Drupal\graphql\Plugin\GraphQL\Scalars\Internal;

use Drupal\graphql\Plugin\GraphQL\Scalars\ScalarPluginBase;
use Drupal\graphql\Plugin\SchemaBuilderInterface;
use Drupal\graphql\Plugin\TypePluginManager;
use GraphQL\Type\Definition\IntType;

/**
 * @GraphQLScalar(
 *   id = "timestamp",
 *   name = "Timestamp",
 *   type = "timestamp"
 * )
 */
class TimestampScalar extends ScalarPluginBase {

  /**
   * {@inheritdoc}
   */
  public static function createInstance(SchemaBuilderInterface $builder, TypePluginManager $manager, $definition, $id) {
    return new IntType([
      'name' => 'Timestamp',
    ]);
  }

}
