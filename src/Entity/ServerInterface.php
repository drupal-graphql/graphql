<?php

namespace Drupal\graphql\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

interface ServerInterface extends ConfigEntityInterface {

  /**
   * Retrieves the query endpoint of this server.
   *
   * @return string
   *   The query endpoint of this server.
   */
  public function endpoint();

  /**
   * Retrieves the server configuration
   *
   * @return \GraphQL\Server\ServerConfig
   *   The server configuration.
   */
  public function configuration();

}
