<?php

namespace Drupal\graphql\Entity;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

interface ServerInterface extends ConfigEntityInterface {

  /**
   * Retrieves the server configuration
   *
   * @return \GraphQL\Server\ServerConfig
   *   The server configuration.
   */
  public function configuration();

}
