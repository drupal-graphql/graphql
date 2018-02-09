<?php

namespace Drupal\graphql_core\Plugin\GraphQL\Interfaces\Routing;

use Drupal\graphql\Plugin\GraphQL\Interfaces\InterfacePluginBase;

/**
 * GraphQL interface for Urls.
 *
 * @GraphQLInterface(
 *   id = "url",
 *   name = "Url",
 *   description = @Translation("Common interface for internal and external urls.")
 * )
 */
class Url extends InterfacePluginBase {

}
