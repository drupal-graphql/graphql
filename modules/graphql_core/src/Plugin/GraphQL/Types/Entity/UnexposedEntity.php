<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Types\Entity;

use Drupal\graphql\Plugin\GraphQL\Types\TypePluginBase;
use Youshido\GraphQL\Execution\ResolveInfo;

/**
 * Entity type to resolve to if the values type is not exposed to the schema.
 *
 * If a field value is an Entity, but the type and/or bundle are not part of
 * the GraphQL schema, this hidden entity type will be resolved.
 *
 * It exposes only the uuid property, which won't leak sensitive information,
 * but might be useful for debugging results.
 *
 * @GraphQLType(
 *   id = "unexposed_entity",
 *   name = "UnexposedEntity",
 *   description = @Translation("Fallback type for otherwise unexposed entities."),
 *   weight = -10,
 *   interfaces = {"Entity"}
 * )
 */
class UnexposedEntity extends TypePluginBase {

  /**
   * {@inheritdoc}
   */
  public function applies($value, ResolveInfo $info = NULL) {
    return TRUE;
  }

}
