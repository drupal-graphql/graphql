<?php

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\Tests\graphql\Kernel\GraphQLTestBase;

/**
 * Test schema validation.
 *
 * @group graphql
 */
class SchemaValidationTest extends GraphQLTestBase {

  public function testValidSchema() {
    $schema = <<<GQL
      schema {
        query: Query
      }
      
      type Query {
        foo: String
      }
GQL;

    $this->setUpSchema($schema);
    $this->mockResolver('Query', 'foo', 'bar');

    $this->assertTrue($this->schema->validateSchema());
  }

  public function testSyntaxError() {
    $schema = <<<GQL
      schema {
        query: Query
      }
      
      type Query {
        foo: String
      }
      
      error
GQL;

    $this->setUpSchema($schema);
    $this->mockResolver('Query', 'foo', 'bar');

    $this->assertFalse($this->schema->validateSchema());

    /** @var \Drupal\Core\Messenger\Messenger $messenger */
    $messenger = \Drupal::service('messenger');
    $messages = $messenger->all();
    $this->assertArrayHasKey('error', $messages);
    $this->assertEquals("Syntax error in schema: Syntax Error: Unexpected Name \"error\"", $messages['error'][0]);
  }

  public function testMissingFieldError() {
    $schema = <<<GQL
      schema {
        query: Query
      }
      
      type Query {
        bar: String
      }
GQL;

    $this->setUpSchema($schema);
    $this->mockResolver('Query', 'foo', 'bar');

    $this->assertFalse($this->schema->validateSchema());

    /** @var \Drupal\Core\Messenger\Messenger $messenger */
    $messenger = \Drupal::service('messenger');
    $messages = $messenger->all();
    $this->assertArrayHasKey('error', $messages);
    $this->assertEquals("Potentially missing field resolver for field bar on type Query. The query engine will try to resolve the field using the default field resolver.", $messages['error'][0]);
  }

  public function testMissingTypeError() {
    $schema = <<<GQL
      schema {
        query: Query
      }
      
      type Query {
        foo: Foo
      }
GQL;

    $this->setUpSchema($schema);
    $this->mockResolver('Query', 'foo', NULL);

    $this->assertFalse($this->schema->validateSchema());

    /** @var \Drupal\Core\Messenger\Messenger $messenger */
    $messenger = \Drupal::service('messenger');
    $messages = $messenger->all();
    $this->assertArrayHasKey('error', $messages);
    $this->assertEquals("Schema validation error: Type \"Foo\" not found in document.", $messages['error'][0]);
  }

}
