<?php

declare(strict_types=1);

namespace Drupal\graphql_schema_extension_test\Plugin\GraphQL\SchemaExtension;

/**
 * A test extension with high priority.
 *
 * This should be executed before plugins with lower priority.
 *
 * @SchemaExtension(
 *   id = "high_priority_test",
 *   name = "High priority test",
 *   schema = "composable",
 *   priority = 10
 * )
 */
class HighPriorityTestExtension extends TestExtension {}
