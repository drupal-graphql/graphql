<?php

declare(strict_types=1);

namespace Drupal\graphql\Event;

use Drupal\Component\EventDispatcher\Event;

/**
 * Represents an event that is triggered to alter schema extension data.
 */
class AlterSchemaExtensionDataEvent extends Event {

  /**
   * Event fired to alter schema extension data.
   */
  const EVENT_NAME = 'graphql.sdl.alter_schema_extension';

  /**
   * AlterSchemaExtensionDataEvent constructor.
   *
   * @param array<array-key, \GraphQL\Language\AST\DocumentNode> $schemaExtensionData
   *   The schema extension data, keyed by plugin ID or extension index.
   */
  public function __construct(
    protected array $schemaExtensionData,
  ) {
  }

  /**
   * Returns the schema extension data.
   *
   * @return array<array-key, \GraphQL\Language\AST\DocumentNode>
   *   The schema extension data, keyed by plugin ID or extension index.
   */
  public function getSchemaExtensionData(): array {
    return $this->schemaExtensionData;
  }

  /**
   * Returns the schema extension data.
   *
   * @param array<array-key, \GraphQL\Language\AST\DocumentNode> $schemaExtensionData
   *   The schema extension data, keyed by plugin ID or extension index.
   */
  public function setSchemaExtensionData(array $schemaExtensionData): void {
    $this->schemaExtensionData = $schemaExtensionData;
  }

}
