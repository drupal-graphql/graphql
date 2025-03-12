<?php

declare(strict_types=1);

namespace Drupal\graphql_schema_extension_test\Plugin\GraphQL\SchemaExtension;

/**
 * A test extension with low priority.
 *
 * This should be executed after plugins with higher priority.
 *
 * @SchemaExtension(
 *   id = "low_priority_test",
 *   name = "Low priority test",
 *   schema = "composable",
 *   priority = -10
 * )
 */
class LowPriorityTestExtension extends TestExtension {}
