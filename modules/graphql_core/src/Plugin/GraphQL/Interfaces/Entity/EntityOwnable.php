<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Interfaces\Entity;

use Drupal\graphql\Plugin\GraphQL\Interfaces\InterfacePluginBase;

/**
 * @GraphQLInterface(
 *   id = "entity_ownable",
 *   name = "EntityOwnable",
 *   interfaces = {"Entity"},
 *   description = @Translation("Common interface for entities that have a owner."),
 *   provider = "user"
 * )
 */
class EntityOwnable extends InterfacePluginBase {

}
