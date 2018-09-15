<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Interfaces\Entity;

use Drupal\graphql\Plugin\GraphQL\Interfaces\InterfacePluginBase;

/**
 * @GraphQLInterface(
 *   id = "entity_revisionable",
 *   name = "EntityRevisionable",
 *   interfaces = {"Entity"},
 *   description = @Translation("Common interface for entities that are revisionable.")
 * )
 */
class EntityRevisionable extends InterfacePluginBase {

}
