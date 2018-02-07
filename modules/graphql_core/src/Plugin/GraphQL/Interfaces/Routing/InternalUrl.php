<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Interfaces\Routing;

use Drupal\graphql\Plugin\GraphQL\Interfaces\InterfacePluginBase;

/**
 * @GraphQLInterface(
 *   id = "internal_url",
 *   name = "InternalUrl",
 *   description = @Translation("Common interface for internal urls."),
 *   interfaces = {"Url"}
 * )
 */
class InternalUrl extends InterfacePluginBase {

}
