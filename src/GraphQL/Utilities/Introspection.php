<?php

namespace Drupal\graphql\GraphQL\Utilities;

use Youshido\GraphQL\Execution\Processor;
use Drupal\graphql\SchemaFactory;

/**
 * Class Introspection.
 *
 * @package Drupal\graphql\GraphQL\Utilities
 */
class Introspection {
  private $introspectionQuery = <<<TEXT
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
   * IntrospectionService constructor.
   *
   * @param \Drupal\graphql\SchemaFactory $schema_factory
   *   Schema Factory.
   */
  public function __construct(SchemaFactory $schema_factory) {
    $this->schema = $schema_factory->getSchema();
  }

  /**
   * Perform an introspection query and return result.
   *
   * @return array
   *   The introspection result as an array.
   */
  public function introspect() {
    $processor = new Processor($this->schema);

    $processor->processPayload($this->introspectionQuery, []);

    return $processor->getResponseData();
  }

}
