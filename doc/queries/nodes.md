# Querying nodes

One common scenarios you will run into is to query a node together with certain fields. We have seen already a couple of things that can give a good overview on how to do this, but lets take an example again of the "Article" type and get some fields out of it like the `id` and `label` but also a custom field `creator` which is just a normal text field in this content type.

Before this make sure to read the introduction and how to add you own custom schema, only after that you can start adding resolvers and extending your DSL with your own types.

## Add the schema declaration

The first step as seen in the introduction is to add the types and fields in the schema. We can do this directly in the schema string in your own schema implementation.

```
schema {
    query: Query
}

type Query {
    ...
    article(id: Int!): Article
    ...
}

type Article implements NodeInterface {
    id: Int!
    title: String!
    creator: String
}

...

interface NodeInterface {
    id: Int!
}
```
Now we have an article in the schema with 3 fields `id`, `label` and our custom field `creator`. We can start adding resolvers for each of them.

## Adding resolvers

To add the resolvers we go to our schema implementation and call the appropriate data producers inside the `getResolverRegistry` method.

```php
/**
   * {@inheritdoc}
   */
  protected function getResolverRegistry() {
    $builder = new ResolverBuilder();
    $registry = new ResolverRegistry([
      'Article' => ContextDefinition::create('entity:node')
        ->addConstraint('Bundle', 'article'),
    ]);

    $registry->addFieldResolver('Query', 'article',
      $builder->produce('entity_load', ['mapping' => [
        'type' => $builder->fromValue('node'),
        'bundles' => $builder->fromValue(['article']),
        'id' => $builder->fromArgument('id'),
      ]])
    );

    $registry->addFieldResolver('Article', 'id',
      $builder->produce('entity_id', ['mapping' => [
        'entity' => $builder->fromParent(),
      ]])
    );

    $registry->addFieldResolver('Article', 'title',
      $builder->produce('entity_label', ['mapping' => [
        'entity' => $builder->fromParent(),
      ]])
    );

    $registry->addFieldResolver('Article', 'creator',
      $builder->produce('property_path', [
        'mapping' => [
          'type' => $builder->fromValue('entity:node'),
          'value' => $builder->fromParent(),
          'path' => $builder->fromValue('field_article_creator.value'),
        ],
      ])
    );

    return $registry;
  }
```

