<?php

namespace Drupal\graphql_image\Plugin\GraphQL\Types;

use Drupal\graphql_core\GraphQL\TypePluginBase;

/**
 * GraphQL Type for Drupal image fields.
 *
 * @GraphQLType(
 *   id = "image",
 *   name = "Image",
 *   interfaces = {"ImageResource"}
 * )
 */
class Image extends TypePluginBase {

}
