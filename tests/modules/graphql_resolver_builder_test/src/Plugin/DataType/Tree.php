<?php

namespace Drupal\graphql_resolver_builder_test\Plugin\DataType;

use Drupal\Core\TypedData\Plugin\DataType\Map;

/**
 * A tree data type for testing.
 *
 * @DataType(
 *   id = "tree",
 *   label = @Translation("Tree"),
 *   definition_class = "\Drupal\graphql_resolver_builder_test\TypedData\Definition\TreeDefinition"
 * )
 */
class Tree extends Map {}
