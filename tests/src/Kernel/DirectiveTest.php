<?php

declare(strict_types=1);

namespace Drupal\Tests\graphql\Kernel;

use GraphQL\Executor\Values;
use GraphQL\Type\Definition\Directive;
use GraphQL\Type\Definition\FieldDefinition;
use GraphQL\Type\Definition\ObjectType;

/**
 * Tests the alterable schema.
 *
 * @group graphql
 */
class DirectiveTest extends GraphQLTestBase {

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $schema = <<<GQL
      directive @exampleDirective on OBJECT | FIELD_DEFINITION

      schema {
        query: Query
      }
      type Query {
        node(id: Int): Node @exampleDirective
        nodeExtended(id: Int): NodeExtended
      }

      type Node @exampleDirective {
        id: Int
      }
      type NodeExtended {
        id: Int
      }
    GQL;

    $extension = <<<GQL
      extend type NodeExtended @exampleDirective {
        title: String
      }
      extend type Query {
        nodeExtended(id: Int): NodeExtended @exampleDirective
      }
    GQL;

    $this->setUpSchema($schema, 'test', [], [
      $this->mockSchemaExtension('test_extension', NULL, $extension),
    ]);
    $this->mockResolver('Query', 'node', function () {
      return ['id' => 1];
    });
    $this->mockResolver('Query', 'nodeExtended', function () {
      return ['id' => 1];
    });
  }

  /**
   * Tests that a directive on a base type is preserved on the built schema.
   */
  public function testDirectiveExistsOnSchemaObject(): void {
    $schema = $this->schema->getSchema();

    $directive = $schema->getDirective('exampleDirective');
    $this->assertNotNull($directive);
    $object = $schema->getType('Node');
    $this->assertInstanceOf(ObjectType::class, $object);
    $this->assertHasDirective($directive, $object);
  }

  /**
   * Tests that a directive on a base field is preserved on the built schema.
   */
  public function testDirectiveExistsOnSchemaField(): void {
    $schema = $this->schema->getSchema();

    $directive = $schema->getDirective('exampleDirective');
    $this->assertNotNull($directive);
    $field = $schema->getQueryType()->getField('node');
    $this->assertHasDirective($directive, $field);
  }

  /**
   * Tests that a directive on an extended type is preserved.
   */
  public function testDirectiveExistsOnExtendedSchemaObject(): void {
    $schema = $this->schema->getSchema();

    $directive = $schema->getDirective('exampleDirective');
    $this->assertNotNull($directive);
    $object = $schema->getType('NodeExtended');
    $this->assertInstanceOf(ObjectType::class, $object);
    $this->assertHasDirective($directive, $object);
  }

  /**
   * Tests that a directive on an extended field is preserved.
   */
  public function testDirectiveExistsOnExtendedSchemaField(): void {
    $schema = $this->schema->getSchema();

    $directive = $schema->getDirective('exampleDirective');
    $this->assertNotNull($directive);
    $field = $schema->getQueryType()->getField('nodeExtended');
    $this->assertHasDirective($directive, $field);
  }

  /**
   * Asserts that the directive exists on the node's AST or any extension AST.
   *
   * @param \GraphQL\Type\Definition\Directive $directive
   *   The directive to look for.
   * @param \GraphQL\Type\Definition\FieldDefinition|\GraphQL\Type\Definition\ObjectType $node
   *   The schema element whose AST nodes should be inspected.
   */
  public function assertHasDirective(Directive $directive, FieldDefinition|ObjectType $node): void {
    $this->assertNotNull($node->astNode);
    $hasAny = Values::getDirectiveValues($directive, $node->astNode) !== NULL;
    foreach ($node->extensionASTNodes ?? [] as $extensionASTNode) {
      if (Values::getDirectiveValues($directive, $extensionASTNode) !== NULL) {
        $hasAny = TRUE;
      }
    }
    $this->assertTrue($hasAny, 'Could not find directive on astNode or extensionAstNode.');
  }

}
