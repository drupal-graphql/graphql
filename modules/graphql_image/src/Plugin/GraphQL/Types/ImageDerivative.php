<?php

namespace Drupal\graphql_image\Plugin\GraphQL\Types;

use Drupal\graphql_core\GraphQL\TypePluginBase;

/**
 * GraphQL Type for Drupal image derivatives.
 *
 * @GraphQLType(
 *   id = "image_derivative",
 *   name = "ImageDerivative",
 *   interfaces = {"ImageResource"}
 * )
 */
class ImageDerivative extends TypePluginBase {

}
