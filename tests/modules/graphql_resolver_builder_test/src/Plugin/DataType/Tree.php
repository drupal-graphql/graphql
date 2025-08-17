<?php

declare(strict_types=1);

namespace Drupal\graphql_resolver_builder_test\Plugin\DataType;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\TypedData\Attribute\DataType;
use Drupal\Core\TypedData\Plugin\DataType\Map;
use Drupal\graphql_resolver_builder_test\TypedData\Definition\TreeDefinition;

/**
 * A tree data type for testing.
 *
 * @DataType(
 *   id = "tree",
 *   label = @Translation("Tree"),
 *   definition_class = "\Drupal\graphql_resolver_builder_test\TypedData\Definition\TreeDefinition"
 * )
 */
#[DataType(
  id: "tree",
  label: new TranslatableMarkup("Tree"),
  definition_class: TreeDefinition::class
)]
class Tree extends Map {}
