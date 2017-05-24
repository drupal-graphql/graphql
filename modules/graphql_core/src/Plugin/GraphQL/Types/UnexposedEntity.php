<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Types;

use Drupal\graphql_core\GraphQL\TypePluginBase;

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
 *   interfaces = {"Entity"}
 * )
 */
class UnexposedEntity extends TypePluginBase {

}
