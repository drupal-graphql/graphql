<?php

namespace Drupal\graphql\Plugin\GraphQL\Schema;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistry;

/**
 * @Schema(
 *   id = "test",
 *   name = "Test schema"
 * )
 * @codeCoverageIgnore
 */
class SdlSchemaTest extends SdlSchemaPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function getSchemaDefinition() {
    return <<<GQL
      schema {
        query: Query
      }

      type Query {
        article(id: Int!): Article
        page(id: Int!): Page
        node(id: Int!): NodeInterface
        label(id: Int!): String
      }

      type Article implements NodeInterface {
        id: Int!
        uid: String
        title: String!
        render: String
      }

      type Page implements NodeInterface {
        id: Int!
        uid: String
        title: String
      }

      interface NodeInterface {
        id: Int!
      }
GQL;
  }

  /**
   * {@inheritdoc}
   */
  public function getResolverRegistry() {
    $builder = new ResolverBuilder();
    $registry = new ResolverRegistry();

    $registry->addFieldResolver('Query', 'node',
      $builder->produce('entity_load')
        ->map('type', $builder->fromValue('node'))
        ->map('id', $builder->fromArgument('id'))
    );

    $registry->addFieldResolver('Query', 'label',
      $builder->produce('entity_label')
        ->map('entity', $builder->produce('entity_load')
          ->map('type', $builder->fromValue('node'))
          ->map('bundles', $builder->fromValue(['article']))
          ->map('id', $builder->fromArgument('id'))
        )
    );

    $registry->addFieldResolver('Query', 'article',
      $builder->produce('entity_load')
        ->map('type', $builder->fromValue('node'))
        ->map('bundles', $builder->fromValue(['article']))
        ->map('id', $builder->fromArgument('id'))
    );

    $registry->addFieldResolver('Query', 'page',
      $builder->produce('entity_load')
        ->map('type', $builder->fromValue('node'))
        ->map('bundles', $builder->fromValue(['page']))
        ->map('id', $builder->fromArgument('id'))
    );

    $registry->addFieldResolver('Article', 'id',
      $builder->produce('entity_id')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('Article', 'title',
      $builder->compose(
        $builder->produce('entity_label')
          ->map('entity', $builder->fromParent()),
        $builder->produce('uppercase')
          ->map('string', $builder->fromParent())
      )
    );

    $registry->addFieldResolver('Article', 'render',
      $builder->produce('entity_rendered')
        ->map('entity', $builder->fromParent())
        ->map('mode', $builder->fromValue('full'))
    );

    $registry->addFieldResolver('Article', 'id',
      $builder->produce('entity_id')
        ->map('entity', $builder->fromParent())
    );

    return $registry;
  }
}
