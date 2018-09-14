<?php

namespace Drupal\graphql\GraphQL;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use GraphQL\Type\Definition\ResolveInfo;
use GraphQL\Type\Schema;

interface ResolverRegistryInterface {

  /**
   * Validate compliance with the provided schema.
   *
   * @param \GraphQL\Type\Schema $schema
   *   The schema to perform validation against.
   *
   * @return null|array
   *   An array of compliance violations or NULL if the registry fully complies
   *   with the schema.
   */
  public function validateCompliance(Schema $schema);

  /**
   * @param $value
   * @param $args
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *
   * @return callable|null
   */
  public function resolveField($value, $args, ResolveContext $context, ResolveInfo $info);

  /**
   * @param $value
   * @param \Drupal\graphql\GraphQL\Execution\ResolveContext $context
   * @param \GraphQL\Type\Definition\ResolveInfo $info
   *
   * @return callable|null
   */
  public function resolveType($value, ResolveContext $context, ResolveInfo $info);

}