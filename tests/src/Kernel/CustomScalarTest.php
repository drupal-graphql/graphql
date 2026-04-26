<?php

declare(strict_types=1);

namespace Drupal\Tests\graphql\Kernel;

use Drupal\graphql\GraphQL\CustomScalarInterface;
use Drupal\graphql\GraphQL\ResolverBuilder;
use GraphQL\Language\AST\Node;

/**
 * Tests the custom scalar functionality.
 *
 * @group graphql
 */
class CustomScalarTest extends GraphQLTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'graphql_dataproducers_test',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $schema = <<<GQL
      schema {
        query: Query
      }
      scalar TestTuple

      type Query {
        read: TestTuple
        compare(input: TestTuple): Boolean
      }
    GQL;

    $this->setUpSchema($schema);
    $this->registry->addCustomScalar(
      'TestTuple',
      new TestTupleScalar(),
    );
    $this->mockResolver('Query', 'read', ['foo', 'bar']);
    $builder = new ResolverBuilder();
    $this->mockResolver('Query', 'compare',
      $builder->produce('test_compare_tuple')
        ->map('input', $builder->fromArgument('input'))
    );
  }

  /**
   * Test that serialization of value into GraphQL response works.
   */
  public function testTupleSerializes(): void {
    $this->assertResults(
      <<<GQL
      query {
        read
      }
      GQL,
      [],
      [
        'read' => 'foo:bar',
      ],
    );
  }

  /**
   * Test that input deserialization into PHP type works.
   */
  public function testTupleDeserializes(): void {
    $this->assertResults(
      <<<GQL
      query (\$input: TestTuple) {
        compare(input: \$input)
      }
      GQL,
      [
        'input' => 'same:same',
      ],
      [
        'compare' => TRUE,
      ],
    );
    $this->assertResults(
      <<<GQL
      query (\$input: TestTuple) {
        compare(input: \$input)
      }
      GQL,
      [
        'input' => 'same:not-same',
      ],
      [
        'compare' => FALSE,
      ],
    );
  }

  /**
   * Test that custom scalar implementations must be serializable.
   */
  public function testRejectsNonSerializable(): void {
    $this->expectException(\InvalidArgumentException::class);
    $this->expectExceptionMessage("must be serializable");

    $this->registry->addCustomScalar(
      "NotSerializable",
      new class implements CustomScalarInterface {

        /**
         * {@inheritdoc}
         */
        public function serialize(mixed $value): string {
          return 'foo';
        }

        /**
         * {@inheritdoc}
         */
        public function parseValue(mixed $value): string {
          return 'bar';
        }

        /**
         * {@inheritdoc}
         */
        public function parseLiteral(Node $valueNode, ?array $variables = NULL): array {
          return ['bar', 'foo'];
        }

      },
    );
  }

}
