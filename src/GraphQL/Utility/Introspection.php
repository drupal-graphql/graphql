<?php

namespace Drupal\graphql\GraphQL\Utility;

use Drupal\graphql\Entity\ServerInterface;
use GraphQL\Server\OperationParams;
use GraphQL\Type\Introspection as IntrospectionType;

class Introspection {

  /**
   * Perform an introspection query and return result.
   *
   * @param \Drupal\graphql\Entity\ServerInterface $server
   *   The server instance.
   *
   * @return array The introspection result as an array.
   *   The introspection result as an array.
   *
   */
  public function introspect(ServerInterface $server) {
    $operation = new OperationParams();
    $operation->query = IntrospectionType::getIntrospectionQuery(['descriptions' => TRUE]);

    $result = $server->executeOperation($operation);
    return $result->toArray();
  }

}
