<?php

namespace Drupal\graphql\Plugin\GraphQL\Scalars;

use Drupal\graphql\GraphQL\Type\UploadType;
use Drupal\graphql\Plugin\GraphQL\PluggableSchemaBuilderInterface;

/**
 * @GraphQLScalar(
 *   id = "upload",
 *   name = "Upload"
 * )
 */
class GraphQLUpload extends ScalarPluginBase {

  /**
   * {@inheritdoc}
   */
  public function getDefinition(PluggableSchemaBuilderInterface $schemaBuilder) {
    if (!isset($this->definition)) {
      $this->definition = new UploadType();
    }

    return $this->definition;
  }
}
