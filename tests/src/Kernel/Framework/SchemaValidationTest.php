<?php

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use Drupal\graphql\GraphQL\ResolverBuilder;

/**
 * Test schema validation.
 *
 * @group graphql
 */
class SchemaValidationTest extends GraphQLTestBase {

  public function testValidSchema() {
    $gql_schema = <<<GQL
      schema {
        query: Query
      }
      type Query {
        foo: String
      }
GQL;
    $this->setUpSchema($gql_schema, $this->getDefaultSchema());
    $builder = new ResolverBuilder();
    $this->mockField('foo', ['parent' => 'Query' ], $builder->fromValue('bar'));

    $this->assertTrue($this->schema->validateSchema());
  }

  public function testSyntaxError() {
    $gql_schema = <<<GQL
      schema {
        query: Query
      }
      type Query {
        foo: String
      }
      error
GQL;
    $this->setUpSchema($gql_schema, $this->getDefaultSchema());
    $builder = new ResolverBuilder();
    $this->mockField('foo', ['parent' => 'Query' ], $builder->fromValue('bar'));

    $this->assertFalse($this->schema->validateSchema());

    /** @var \Drupal\Core\Messenger\Messenger $messenger */
    $messenger = \Drupal::service('messenger');
    $messages = $messenger->all();
    $this->assertArrayHasKey('error', $messages);
    $this->assertEquals("Syntax error in schema: Syntax Error: Unexpected Name \"error\"", $messages['error'][0]);
  }

  public function testMissingFieldError() {
    $gql_schema = <<<GQL
      schema {
        query: Query
      }
      type Query {
        bar: String
      }
GQL;
    $this->setUpSchema($gql_schema, $this->getDefaultSchema());
    $builder = new ResolverBuilder();
    $this->mockField('foo', ['parent' => 'Query' ], $builder->fromValue('bar'));

    $this->assertFalse($this->schema->validateSchema());

    /** @var \Drupal\Core\Messenger\Messenger $messenger */
    $messenger = \Drupal::service('messenger');
    $messages = $messenger->all();
    $this->assertArrayHasKey('error', $messages);
    $this->assertEquals("Potentially missing field resolver for field bar on type Query. The query engine will try to resolve the field using the default field resolver.", $messages['error'][0]);
  }

  public function testMissingTypeError() {
    $gql_schema = <<<GQL
      schema {
        query: Query
      }
      type Query {
        foo: Foo
      }
GQL;
    $this->setUpSchema($gql_schema, $this->getDefaultSchema());
    $builder = new ResolverBuilder();
    $this->mockField('foo', ['parent' => 'Query' ], $builder->fromValue(NULL));

    $this->assertFalse($this->schema->validateSchema());

    /** @var \Drupal\Core\Messenger\Messenger $messenger */
    $messenger = \Drupal::service('messenger');
    $messages = $messenger->all();
    $this->assertArrayHasKey('error', $messages);
    $this->assertEquals("Schema validation error: Type \"Foo\" not found in document.", $messages['error'][0]);
  }

}
