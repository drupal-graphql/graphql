# Querying nodes

A common scenario into which you will run often is to query a node together with certain field. We have already seen a couple of things that can give a good overview on how to do this. Let's take our example of the "Article" type again and query some fields like the `id`, the `label` but also the custom field `creator` which is just a normal text field.

Before you start, make sure to read the introduction and how to add a custom schema, only after that you should start adding resolvers and extending your schema with your own types.

## Add the schema declaration

The first step, as seen in the introduction, is to add the types and fields in the schema. You can add this directly into schema string in your own schema implementation (`src/Plugin/GraphQL/Schema/SdlSchemaMyDrupalGql.php`).

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

Now we have an "Article" type in the schema with three fields `id`, `label` and our custom field `creator`. The next step is to add resolvers for each of them.

## Adding resolvers

To add the resolvers we go to our schema implementation and call the appropriate data producers inside the `getResolverRegistry` method. Because our types are extending a common `NodeInterface` we need to also tell what to resolve for a particular type, otherwise it could be an Article or a Page.

```php
use GraphQL\Error\Error;
...

/**
 * {@inheritdoc}
 */
protected function getResolverRegistry() {
  $builder = new ResolverBuilder();
  $registry = new ResolverRegistry([
    'Article' => ContextDefinition::create('entity:node')
      ->addConstraint('Bundle', 'article'),
  ]);

  // Tell GraphQL how to resolve types of a common interface.
  $registry->addTypeResolver('NodeInterface', function ($value) {
    if ($value instanceof NodeInterface) {
      switch ($value->bundle()) {
        case 'article': return 'Article';
        case 'page': return 'Page';
      }
    }
    throw new Error('Could not resolve content type.');
  });

  $registry->addFieldResolver('Query', 'article',
    $builder->produce('entity_load')
      ->map('type', $builder->fromValue('node'))
      ->map('bundles', $builder->fromValue(['article']))
      ->map('id', $builder->fromArgument('id'))
    ]])
  );

  $registry->addFieldResolver('Article', 'id',
    $builder->produce('entity_id')
      ->map('entity', $builder->fromParent())
  );

  $registry->addFieldResolver('Article', 'title',
    $builder->produce('entity_label')
      ->map('entity', $builder->fromParent())
  );

  $registry->addFieldResolver('Article', 'creator',
    $builder->produce('property_path')
      ->map('type', $builder->fromValue('entity:node'))
      ->map('value', $builder->fromParent())
      ->map('path', $builder->fromValue('field_article_creator.value'))
  );

  return $registry;
}
```
