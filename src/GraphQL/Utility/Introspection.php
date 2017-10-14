<?php

namespace Drupal\graphql\GraphQL\Utility;

use Drupal\graphql\GraphQL\Execution\Processor;
use Drupal\graphql\GraphQL\Execution\QueryProcessor;
use Drupal\graphql\GraphQL\Reducers\ReducerManager;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Youshido\GraphQL\Schema\AbstractSchema;

class Introspection {
  protected $introspectionQuery = <<<TEXT
    query IntrospectionQuery {
      __schema {
        queryType { name }
        mutationType { name }
        subscriptionType { name }
        types {
          ...FullType
        }
        directives {
          name
          description
          locations
          args {
            ...InputValue
          }
        }
      }
    }

    fragment FullType on __Type {
      kind
      name
      description
      fields(includeDeprecated: true) {
        name
        description
        args {
          ...InputValue
        }
        type {
          ...TypeRef
        }
        isDeprecated
        deprecationReason
      }
      inputFields {
        ...InputValue
      }
      interfaces {
        ...TypeRef
      }
      enumValues(includeDeprecated: true) {
        name
        description
        isDeprecated
        deprecationReason
      }
      possibleTypes {
        ...TypeRef
      }
    }

    fragment InputValue on __InputValue {
      name
      description
      type { ...TypeRef }
      defaultValue
    }

    fragment TypeRef on __Type {
      kind
      name
      ofType {
        kind
        name
        ofType {
          kind
          name
          ofType {
            kind
            name
            ofType {
              kind
              name
              ofType {
                kind
                name
                ofType {
                  kind
                  name
                  ofType {
                    kind
                    name
                  }
                }
              }
            }
          }
        }
      }
    }
TEXT;

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
    $result = $this->queryProcessor->processQuery($schema, $this->introspectionQuery);
    return $result->getData();
  }

}
