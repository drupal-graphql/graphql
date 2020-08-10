<?php

namespace Drupal\graphql_composable\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\GraphQL\Response\ResponseInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;
use Drupal\graphql_composable\GraphQL\Response\ArticleResponse;

/**
 * @SchemaExtension(
 *   id = "composable_extension",
 *   name = "Composable Example extension",
 *   description = "A simple extension that adds node related fields.",
 *   schema = "composable"
 * )
 */
class ComposableSchemaExampleExtension extends SdlSchemaExtensionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry) {
    $builder = new ResolverBuilder();

    $registry->addFieldResolver('Query', 'article',
      $builder->produce('entity_load')
        ->map('type', $builder->fromValue('node'))
        ->map('bundles', $builder->fromValue(['article']))
        ->map('id', $builder->fromArgument('id'))
    );

    // Create article mutation.
    $registry->addFieldResolver('Mutation', 'createArticle',
      $builder->produce('create_article')
        ->map('data', $builder->fromArgument('data'))
    );

    $registry->addFieldResolver('ArticleResponse', 'article',
      $builder->callback(function (ArticleResponse $response) {
        return $response->article();
      })
    );

    $registry->addFieldResolver('ArticleResponse', 'errors',
      $builder->callback(function (ArticleResponse $response) {
        return $response->getViolations();
      })
    );

    $registry->addFieldResolver('Article', 'id',
      $builder->produce('entity_id')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('Article', 'title',
      $builder->compose(
        $builder->produce('entity_label')
          ->map('entity', $builder->fromParent())
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

    // Response type resolver.
    $registry->addTypeResolver('Response', [
      __CLASS__,
      'resolveResponse',
    ]);
  }

  /**
   * Resolves the response type.
   *
   * @param \Drupal\graphql\GraphQL\Response\ResponseInterface $response
   *   Response object.
   *
   * @return string
   *   Response type.
   *
   * @throws \Exception
   *   Invalid response type.
   */
  public static function resolveResponse(ResponseInterface $response): string {
    // Resolve content response.
    if ($response instanceof ArticleResponse) {
      return 'ArticleResponse';
    }
    throw new \Exception('Invalid response type.');
  }

}
