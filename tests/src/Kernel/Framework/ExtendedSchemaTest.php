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

  public function testExtendedSchemaDefinition() {
    $gql_schema = <<<GQL
      schema {
        query: Query
      }
      type Query {
        foo: String
      }
      union Content
GQL;

    $extended_schema = <<<GQL
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

    $expected_printed_schema = <<<GQL
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

    $this->setUpExtendedSchema($gql_schema, $extended_schema, $this->getDefaultSchema());
    $this->assertEquals($this->getPrintedSchema($this->getDefaultSchema()), $expected_printed_schema);

    $builder = new ResolverBuilder();

    $this->mockTypeResolver('Content', function ($value, $context, $info) {
      return $value['id'] < 10 ? 'Article' : 'Recipe';
    });

    $this->mockField('title', [
      'name' => 'title',
      'type' => 'String',
      'parent' => 'Article',
    ], $builder->compose(
      $builder->fromParent(),
      $this->mockCallable(function ($value, $args, ResolveContext $context, ResolveInfo $info) {
        return $value['title'];
      })
    )
    );

    $this->mockField('title', [
      'name' => 'title',
      'type' => 'String',
      'parent' => 'Recipe',
    ], $builder->compose(
      $builder->fromParent(),
      $this->mockCallable(function ($value, $args, ResolveContext $context, ResolveInfo $info) {
        return $value['title'];
      })
    )
    );

    $this->mockField('steps', [
      'name' => 'steps',
      'type' => '[String]',
      'parent' => 'Recipe',
    ], $builder->compose(
      $builder->fromParent(),
      $this->mockCallable(function ($value, $args, ResolveContext $context, ResolveInfo $info) {
        return $value['steps'];
      })
    )
    );

    $this->mockField('bar', [
      'name' => 'bar',
      'type' => '[Content]',
      'parent' => 'Query',
    ], $builder->fromValue(
      [
        ['id' => 8, 'title' => 'Article test'],
        [
          'id' => 11,
          'title' => 'Recipe test',
          'steps' => ['Get ingredients', 'Mix them', 'Serve'],
        ],
      ]
    ));

    $this->assertResults('{ bar { ... on Article { title } ... on Recipe { title steps }  } }', [], [
      'bar' => [
        0 => ['title' => 'Article test'],
        1 => ['title' => 'Recipe test', 'steps' => ['Get ingredients', 'Mix them', 'Serve']],
      ],
    ], $this->defaultCacheMetaData());
  }

}
