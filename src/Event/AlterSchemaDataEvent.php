<?php

declare(strict_types=1);

namespace Drupal\graphql\Event;

use Drupal\Component\EventDispatcher\Event;

/**
 * Represents an event that is triggered to alter schema data.
 */
class AlterSchemaDataEvent extends Event {

  /**
   * Event fired to alter schema data.
   */
  const EVENT_NAME = 'graphql.sdl.alter_schema';

  /**
   * AlterSchemaDataEvent constructor.
   *
   * @param array<array-key, \GraphQL\Language\AST\DocumentNode> $schemaData
   *   The schema data, keyed by plugin ID or extension index.
   */
  public function __construct(
    protected array $schemaData,
  ) {
  }

  /**
   * Returns the schema data.
   *
   * @return array<array-key, \GraphQL\Language\AST\DocumentNode>
   *   The schema data, keyed by plugin ID or extension index.
   */
  public function getSchemaData(): array {
    return $this->schemaData;
  }

  /**
   * Sets the schema data.
   *
   * @param array<array-key, \GraphQL\Language\AST\DocumentNode> $schemaData
   *   The schema data, keyed by plugin ID or extension index.
   */
  public function setSchemaData(array $schemaData): void {
    $this->schemaData = $schemaData;
  }

}
