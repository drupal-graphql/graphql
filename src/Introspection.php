<?php

namespace Drupal\graphql;

use Drupal\graphql\GraphQL\Execution\Processor;
use Drupal\graphql\Reducers\ReducerManager;
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
   * The GraphQL schema.
   *
   * @var \Youshido\GraphQL\Schema\AbstractSchema
   */
  protected $schema;

  /**
   * The dependency injection container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerInterface
   */
  protected $container;

  /**
   * The reducer manager service.
   *
   * @var \Drupal\graphql\Reducers\ReducerManager
   */
  protected $reducerManager;

  /**
   * Constructs an Introspection object.
   *
   * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
   *   The dependency injection container.
   * @param \Drupal\graphql\Reducers\ReducerManager $reducerManager
   *   The reducer manager service.
   * @param \Youshido\GraphQL\Schema\AbstractSchema $schema
   *   The graphql schema.
   */
  public function __construct(ContainerInterface $container, ReducerManager $reducerManager, AbstractSchema $schema) {
    $this->schema = $schema;
    $this->container = $container;
    $this->reducerManager = $reducerManager;
  }

  /**
   * Perform an introspection query and return result.
   *
   * @return array
   *   The introspection result as an array.
   */
  public function introspect() {
    $processor = new Processor($this->container, $this->schema);
    $processor->processPayload($this->introspectionQuery, [], $this->reducerManager->getAllServices());

    return $processor->getResponseData();
  }

}
