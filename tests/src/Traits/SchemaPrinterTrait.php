<?php

namespace Drupal\Tests\graphql\Traits;

use Drupal\graphql\Entity\Server;
use GraphQL\Utils\SchemaPrinter;

/**
 * Trait to get printed version of the schema.
 */
trait SchemaPrinterTrait {

  /**
   * Gets printed version of the schema.
   *
   * @param string $schema
   *   The schema id.
   *
   * @return string
   *   The printed version of the schema.
   */
  protected function getPrintedSchema($schema = 'test') {
    $server = Server::load($schema);
    /** @var \GraphQL\Server\ServerConfig $config */
    $config = $server->configuration();
    $schema = $config->getSchema();
    return SchemaPrinter::doPrint($schema);
  }

}
