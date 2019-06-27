<?php

namespace Drupal\Tests\graphql\Kernel\Framework;

use Drupal\graphql\GraphQL\Execution\ResolveContext;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\Tests\graphql\Kernel\GraphQLTestBase;
use GraphQL\Type\Definition\ResolveInfo;

/**
 * Test extended schema.
 *
 * @group graphql
 */
class ExtendedSchemaTest extends GraphQLTestBase {

  protected function setUp() {
    parent::setUp();

    $base = <<<GQL
      schema {
        query: Query
      }
      
      type Query {
        foo: String
      }
      
      union Content
GQL;

    $extended = <<<GQL
      extend type Query {
        bar: [Content]
      }
      
      type Article {
        title: String!
      }
      
      type Recipe {
        title: String!
        steps: [String]
      }

      extend union Content = Article

      extend union Content = Recipe
GQL;

    $this->setUpExtendedSchema($base, $extended);
  }

  public function testExtendedSchemaDefinition() {
    $expected = <<<GQL
type Article {
  title: String!
}

union Content = Article | Recipe

type Query {
  foo: String
  bar: [Content]
}

type Recipe {
  title: String!
  steps: [String]
}

GQL;

    $this->assertEquals($this->getPrintedSchema(), $expected);
  }

  public function testExtendedSchemaFields() {
    $this->mockTypeResolver('Content', function ($value) {
      return $value['type'];
    });

    $this->mockResolver('Query', 'bar', [
      [
        'type' => 'Article',
        'title' => 'Article test',
      ],
      [
        'type' => 'Recipe',
        'title' => 'Recipe test',
        'steps' => ['Get ingredients', 'Mix them', 'Serve'],
      ],
    ]);

    $query = <<<GQL
      query {
        bar {
          ... on Article {
            title
          }
          
          ... on Recipe {
            title
            steps
          }
        }
      }
GQL;

    $this->assertResults($query, [], [
      'bar' => [
        0 => ['title' => 'Article test'],
        1 => ['title' => 'Recipe test', 'steps' => ['Get ingredients', 'Mix them', 'Serve']],
      ],
    ]);
  }

}
