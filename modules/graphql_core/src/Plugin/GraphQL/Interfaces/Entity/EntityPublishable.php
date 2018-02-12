<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Interfaces\Entity;

use Drupal\graphql\Plugin\GraphQL\Interfaces\InterfacePluginBase;

/**
 * @GraphQLInterface(
 *   id = "entity_publishable",
 *   name = "EntityPublishable",
 *   interfaces = {"Entity"},
 *   description = @Translation("Common interface for entities that are publishable.")
 * )
 */
class EntityPublishable extends InterfacePluginBase {

}
