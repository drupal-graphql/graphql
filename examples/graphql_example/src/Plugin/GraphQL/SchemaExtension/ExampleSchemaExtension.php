<?php

declare(strict_types=1);

namespace Drupal\graphql_examples\Plugin\GraphQL\SchemaExtension;

use Drupal\graphql\Attribute\SchemaExtension;
use Drupal\graphql\GraphQL\ResolverBuilder;
use Drupal\graphql\GraphQL\ResolverRegistryInterface;
use Drupal\graphql\Plugin\GraphQL\SchemaExtension\SdlSchemaExtensionPluginBase;

/**
 * Example schema extension that adds to the mapping.
 */
#[SchemaExtension(
  id: "example_extension",
  name: "Example extension",
  description: "A simple extension that adds node related fields.",
  schema: "example"
)]
class ExampleSchemaExtension extends SdlSchemaExtensionPluginBase {

  /**
   * {@inheritdoc}
   */
  public function registerResolvers(ResolverRegistryInterface $registry): void {
    $builder = new ResolverBuilder();

    $this->addQueryFields($registry, $builder);
    $this->addPageFields($registry, $builder);
  }

  /**
   * Mapping of page fields.
   */
  protected function addPageFields(ResolverRegistryInterface $registry, ResolverBuilder $builder): void {
    $registry->addFieldResolver('Page', 'id',
      $builder->produce('entity_id')
        ->map('entity', $builder->fromParent())
    );

    $registry->addFieldResolver('Page', 'title',
      $builder->compose(
        $builder->produce('entity_label')
          ->map('entity', $builder->fromParent()),
        $builder->produce('uppercase')
          ->map('string', $builder->fromParent())
      )
    );
  }

  /**
   * Mapping of page query fields.
   */
  protected function addQueryFields(ResolverRegistryInterface $registry, ResolverBuilder $builder): void {
    $registry->addFieldResolver('Query', 'page',
      $builder->produce('entity_load')
        ->map('type', $builder->fromValue('node'))
        ->map('bundles', $builder->fromValue(['page']))
        ->map('id', $builder->fromArgument('id'))
    );
  }

}
