<?php

namespace Drupal\graphql_block\Plugin\GraphQL\Types;

use Drupal\graphql_core\GraphQL\TypePluginBase;

/**
 * Simple configuration block type.
 *
 * @GraphQLType(
 *   name = "BlockConfig",
 *   interfaces = {"Block"}
 * )
 */
class BlockConfig extends TypePluginBase {

}
