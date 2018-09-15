<?php

namespace Drupal\graphql\GraphQL\Utility;

use Drupal\graphql\GraphQL\Execution\QueryProcessor;
use GraphQL\Server\OperationParams;
use GraphQL\Type\Introspection as IntrospectionType;

class Introspection {

  /**
   * @var \Drupal\graphql\GraphQL\Execution\QueryProcessor
   */
  protected $queryProcessor;

  /**
   * Constructs an Introspection object.
   *
   * @param \Drupal\graphql\GraphQL\Execution\QueryProcessor $queryProcessor
   *   The query processor srevice.
   */
  public function __construct(QueryProcessor $queryProcessor) {
    $this->queryProcessor = $queryProcessor;
  }

  /**
   * Perform an introspection query and return result.
   *
   * @param string $schema
   *   The name of the graphql schema to introspect.
   *
   * @return array The introspection result as an array.
   *   The introspection result as an array.
   */
  public function introspect($schema) {
    $query = IntrospectionType::getIntrospectionQuery(['descriptions' => TRUE]);
    $operation = OperationParams::create(['query' => $query]);
    $result = $this->queryProcessor->processQuery($schema, $operation);
    return $result->toArray();
  }

}
