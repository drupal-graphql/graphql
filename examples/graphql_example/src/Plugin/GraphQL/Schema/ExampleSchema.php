<?php

declare(strict_types=1);

namespace Drupal\graphql_examples\Plugin\GraphQL\Schema;

use Drupal\graphql\Attribute\Schema;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\Schema\SdlSchemaPluginBase;

/**
 * Example schema plugin that maps article data.
 */
#[Schema(
  id: "example",
  name: "Example schema"
)]
class ExampleSchema extends SdlSchemaPluginBase {

  /**
   * {@inheritdoc}
   */
  protected function registerResolvers(ResolverRegistryInterface $registry): void {
    $builder = new ResolverBuilder();

    $this->addQueryFields($registry, $builder);
    $this->addArticleFields($registry, $builder);

    // Re-usable connection type fields.
    $this->addConnectionFields('ArticleConnection', $registry, $builder);
  }

  /**
   * Mapping of article fields.
   */
  protected function addArticleFields(ResolverRegistryInterface $registry, ResolverBuilder $builder): void {
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

    $registry->addFieldResolver('Article', 'author',
      $builder->compose(
        $builder->produce('entity_owner')
          ->map('entity', $builder->fromParent()),
        $builder->produce('entity_label')
          ->map('entity', $builder->fromParent())
      )
    );
  }

  /**
   * Mapping of query fields.
   */
  protected function addQueryFields(ResolverRegistryInterface $registry, ResolverBuilder $builder): void {
    $registry->addFieldResolver('Query', 'article',
      $builder->produce('entity_load')
        ->map('type', $builder->fromValue('node'))
        ->map('bundles', $builder->fromValue(['article']))
        ->map('id', $builder->fromArgument('id'))
    );

    $registry->addFieldResolver('Query', 'articles',
      $builder->produce('query_articles')
        ->map('offset', $builder->fromArgument('offset'))
        ->map('limit', $builder->fromArgument('limit'))
    );
  }

  /**
   * Mapping of query connection fields.
   */
  protected function addConnectionFields(string $type, ResolverRegistryInterface $registry, ResolverBuilder $builder): void {
    $registry->addFieldResolver($type, 'total',
      $builder->produce('connection_total')
        ->map('connection', $builder->fromParent())
    );

    $registry->addFieldResolver($type, 'items',
      $builder->produce('connection_items')
        ->map('connection', $builder->fromParent())
    );
  }

}
